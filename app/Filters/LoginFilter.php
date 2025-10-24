<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class LoginFilter implements FilterInterface
{
    protected function isAjaxRequest(RequestInterface $request): bool
    {
        return $request->hasHeader('X-Requested-With') && 
               strtolower($request->header('X-Requested-With')->getValue()) === 'xmlhttprequest';
    }

    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        
        log_message('debug', '=== LoginFilter: Authentication Check Started ===');
        log_message('debug', 'Request Details:');
        log_message('debug', '- URI: ' . $request->getUri()->getPath());
        log_message('debug', '- Method: ' . $request->getMethod());
        log_message('debug', 'Session Details:');
        log_message('debug', '- Session ID: ' . session_id());
        log_message('debug', '- Session Data: ' . json_encode($session->get()));

        // Check if user is logged in
        if (!$session->get('logged_in')) {
            log_message('info', 'User not logged in, initiating redirect');

            // Store the current URL for redirect after login
            $currentURL = current_url();
            $loginPath = 'auth/login';
            
            // Only store redirect if not already on login page
            if (!str_contains($currentURL, $loginPath)) {
                log_message('debug', 'Storing redirect URL: ' . $currentURL);
                $session->set('redirect_url', $currentURL);
            }

            // If AJAX request, return JSON response
            if ($this->isAjaxRequest($request)) {
                log_message('debug', 'Returning JSON response for AJAX request');
                return service('response')
                    ->setStatusCode(401)
                    ->setJSON([
                        'success' => false,
                        'message' => 'Session expired. Please login again.',
                        'redirect' => base_url('auth/login')
                    ]);
            }

            // For regular requests, redirect to login page
            log_message('debug', 'Redirecting to login page');
            return redirect()->to(base_url('auth/login'))
                ->with('error', 'Please login to continue.');
        }

        // Check if session is about to expire
        $sessionExpiry = $session->get('__ci_last_regenerate') + (config('Session')->expiration ?? 7200);
        $timeLeft = $sessionExpiry - time();
        
        // If session is close to expiring (within 5 minutes), regenerate it
        if ($timeLeft < 300) {
            log_message('debug', 'Regenerating session - Time left: ' . $timeLeft . ' seconds');
            $session->regenerate(true);
        }

        // User is authenticated, proceed with request
        log_message('debug', 'User is authenticated, proceeding with request');
        log_message('debug', 'User Details:');
        log_message('debug', '- User ID: ' . $session->get('pegawai_id'));
        log_message('debug', '- Username: ' . $session->get('username'));
        log_message('debug', '- Name: ' . $session->get('nama'));
        log_message('debug', '- Is Admin: ' . ($session->get('is_admin') ? 'yes' : 'no'));
        
        return;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Log response status
        log_message('debug', '=== LoginFilter: After Request ===');
        log_message('debug', 'Response Status: ' . $response->getStatusCode());
        
        // Check if response indicates session timeout
        if ($response->getStatusCode() === 401) {
            log_message('debug', 'Session timeout detected, clearing session');
            session()->destroy();
        }
        
        return;
    }
}
