<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use RuntimeException;

class RotateZoomHostTokens extends Migration
{
    public function up()
    {
        if (!$this->db->transBegin()) {
            throw new RuntimeException('Could not start Zoom host token rotation.');
        }

        $committed = false;
        try {
            $replacements = [];
            $lastId = 0;
            $cutoff = date('Y-m-d H:i:s');
            do {
                $meetings = $this->db->table('meeting')
                    ->select('id, start_token')
                    ->where('id >', $lastId)
                    ->where('waktu_selesai >', $cutoff)
                    ->where('start_token !=', '')
                    ->orderBy('id')
                    ->get(100)
                    ->getResultArray();
                foreach ($meetings as $meeting) {
                    $lastId = (int) $meeting['id'];
                    $token = bin2hex(random_bytes(32));
                    $replacements[$meeting['start_token']] = $token;
                    if (!$this->db->table('meeting')->where('id', $lastId)->update(['start_token' => $token])) {
                        throw new RuntimeException('Could not rotate a Zoom host token.');
                    }
                }
            } while (count($meetings) === 100);

            if ($replacements !== []) {
                $this->refreshPendingMessages($replacements);
            }

            $committed = $this->db->transStatus() && $this->db->transCommit();
            if (!$committed) {
                throw new RuntimeException('Could not commit Zoom host token rotation.');
            }
        } finally {
            if (!$committed) {
                $this->db->transRollback();
            }
        }
    }

    private function refreshPendingMessages(array $replacements): void
    {
        $lastId = 0;
        do {
            $rows = $this->db->table('wa_message_queue')
                ->select('id, message')
                ->where('id >', $lastId)
                ->whereIn('status', ['pending', 'processing', 'needs_review'])
                ->like('message', '/zoom/start/')
                ->orderBy('id')
                ->get(100)
                ->getResultArray();
            foreach ($rows as $row) {
                $lastId = (int) $row['id'];
                $message = preg_replace_callback(
                    '~(/zoom/start/)([a-f0-9]+)\b~',
                    static fn (array $match): string => $match[1] . ($replacements[$match[2]] ?? $match[2]),
                    $row['message'],
                );
                if ($message !== $row['message'] && !$this->db->table('wa_message_queue')->where('id', $lastId)->update(['message' => $message])) {
                    throw new RuntimeException('Could not update a queued Zoom host link.');
                }
            }
        } while (count($rows) === 100);
    }

    public function down()
    {
        // Never restore a token that was previously visible to other users.
    }
}
