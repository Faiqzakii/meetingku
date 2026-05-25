<?php

namespace App\Libraries;

class ZoomLibrary
{
    protected $accountId;
    protected $clientId;
    protected $clientSecret;
    protected $enabled;
    protected $password;
    protected $cache;

    public function __construct()
    {
        // Support both Coolify-style UPPER_CASE and compose-style dot.notation env vars
        $this->enabled      = env('zoom.enabled',   env('ZOOM_ENABLED', false));
        $this->accountId    = env('zoom.account_id',    env('ZOOM_ACCOUNT_ID', ''));
        $this->clientId     = env('zoom.client_id',     env('ZOOM_CLIENT_ID', ''));
        $this->clientSecret = env('zoom.client_secret', env('ZOOM_CLIENT_SECRET', ''));
        $this->password     = env('zoom.password',      env('ZOOM_PASSWORD', 'bps6500'));
        $this->cache        = \Config\Services::cache();
    }

    /**
     * Check if Zoom integration is enabled and configured
     */
    public function isEnabled(): bool
    {
        return $this->enabled
            && !empty($this->accountId)
            && !empty($this->clientId)
            && !empty($this->clientSecret);
    }

    /**
     * Get access token via Server-to-Server OAuth
     * Token is cached for 55 minutes (expires in 60)
     */
    public function getAccessToken(): ?string
    {
        // Check cache first
        $cachedToken = $this->cache->get('zoom_access_token');
        if ($cachedToken) {
            return $cachedToken;
        }

        $url = 'https://zoom.us/oauth/token';

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query([
                'grant_type' => 'account_credentials',
                'account_id' => $this->accountId,
            ]),
            CURLOPT_HTTPHEADER     => [
                'Authorization: Basic ' . base64_encode($this->clientId . ':' . $this->clientSecret),
                'Content-Type: application/x-www-form-urlencoded',
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($curlErr) {
            log_message('error', 'Zoom OAuth curl error: ' . $curlErr);
            return null;
        }

        $data = json_decode($response, true);

        if ($httpCode !== 200 || !isset($data['access_token'])) {
            log_message('error', 'Zoom OAuth failed: HTTP ' . $httpCode . ' - ' . $response);
            return null;
        }

        // Cache for 55 minutes (token valid for 60 min)
        $this->cache->save('zoom_access_token', $data['access_token'], 3300);

        log_message('info', 'Zoom access token obtained successfully');
        return $data['access_token'];
    }

    /**
     * Create a Zoom meeting
     *
     * @param string $topic     Meeting topic/title
     * @param string $startTime ISO 8601 format (e.g., 2026-02-14T10:00:00)
     * @param int    $duration  Duration in minutes
     * @param string $agenda    Optional agenda text
     * @return array|null       {id, join_url, start_url} or null on failure
     */
    public function createMeeting(string $topic, string $startTime, int $duration, string $agenda = ''): ?array
    {
        $token = $this->getAccessToken();
        if (!$token) {
            return null;
        }

        $meetingData = [
            'topic'      => $topic,
            'type'       => 2, // Scheduled meeting
            'start_time' => $startTime,
            'duration'   => $duration,
            'timezone'   => 'Asia/Makassar', // WITA (UTC+8)
            'agenda'     => $agenda,
            'settings'   => [
                'host_video'                    => false,
                'participant_video'             => true,
                'join_before_host'              => true,
                'mute_upon_entry'               => true,
                'auto_recording'                => 'none',
                'waiting_room'                  => false,
                'audio'                         => 'voip',
            ],
        ];

        // Add custom password if configured
        if (!empty($this->password)) {
            $meetingData['password'] = $this->password;
        }

        $payload = json_encode($meetingData);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => 'https://api.zoom.us/v2/users/me/meetings',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json',
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($curlErr) {
            log_message('error', 'Zoom createMeeting curl error: ' . $curlErr);
            return null;
        }

        $data = json_decode($response, true);

        if ($httpCode !== 201 || !isset($data['id'])) {
            log_message('error', 'Zoom createMeeting failed: HTTP ' . $httpCode . ' - ' . $response);
            return null;
        }

        log_message('info', 'Zoom meeting created: ID=' . $data['id']);

        return [
            'id'        => (string) $data['id'],
            'join_url'  => $data['join_url'] ?? '',
            'start_url' => $data['start_url'] ?? '',
        ];
    }

    /**
     * Update a Zoom meeting (e.g., reschedule)
     *
     * @param string $meetingId Zoom meeting ID
     * @param array  $data      Fields to update (topic, start_time, duration)
     * @return bool
     */
    public function updateMeeting(string $meetingId, array $data): bool
    {
        $token = $this->getAccessToken();
        if (!$token) {
            return false;
        }

        $payload = [];
        if (isset($data['topic'])) {
            $payload['topic'] = $data['topic'];
        }
        if (isset($data['start_time'])) {
            $payload['start_time'] = $data['start_time'];
        }
        if (isset($data['duration'])) {
            $payload['duration'] = $data['duration'];
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => 'https://api.zoom.us/v2/meetings/' . $meetingId,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_CUSTOMREQUEST  => 'PATCH',
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json',
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($curlErr) {
            log_message('error', 'Zoom updateMeeting curl error: ' . $curlErr);
            return false;
        }

        // 204 No Content = success
        if ($httpCode !== 204) {
            log_message('error', 'Zoom updateMeeting failed: HTTP ' . $httpCode . ' - ' . $response);
            return false;
        }

        log_message('info', 'Zoom meeting updated: ID=' . $meetingId);
        return true;
    }

    /**
     * Delete a Zoom meeting
     *
     * @param string $meetingId Zoom meeting ID
     * @return bool
     */
    public function deleteMeeting(string $meetingId): bool
    {
        $token = $this->getAccessToken();
        if (!$token) {
            return false;
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => 'https://api.zoom.us/v2/meetings/' . $meetingId,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_CUSTOMREQUEST  => 'DELETE',
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json',
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($curlErr) {
            log_message('error', 'Zoom deleteMeeting curl error: ' . $curlErr);
            return false;
        }

        // 204 No Content = success
        if ($httpCode !== 204) {
            log_message('error', 'Zoom deleteMeeting failed: HTTP ' . $httpCode . ' - ' . $response);
            return false;
        }

        log_message('info', 'Zoom meeting deleted: ID=' . $meetingId);
        return true;
    }

    /**
     * Get meeting details (useful to refresh start_url)
     *
     * @param string $meetingId Zoom meeting ID
     * @return array|null
     */
    public function getMeeting(string $meetingId): ?array
    {
        $token = $this->getAccessToken();
        if (!$token) {
            return null;
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => 'https://api.zoom.us/v2/meetings/' . $meetingId,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json',
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($curlErr) {
            log_message('error', 'Zoom getMeeting curl error: ' . $curlErr);
            return null;
        }

        $data = json_decode($response, true);

        if ($httpCode !== 200 || !isset($data['id'])) {
            log_message('error', 'Zoom getMeeting failed: HTTP ' . $httpCode . ' - ' . $response);
            return null;
        }

        return [
            'id'        => (string) $data['id'],
            'join_url'  => $data['join_url'] ?? '',
            'start_url' => $data['start_url'] ?? '',
            'topic'     => $data['topic'] ?? '',
            'start_time' => $data['start_time'] ?? '',
            'duration'  => $data['duration'] ?? 0,
        ];
    }
}
