<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AdminFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        
        log_message('debug', '=== AdminFilter: Authorization Check Started ===');
        log_message('debug', 'Request Details:');
        log_message('debug', '- URI: ' . $request->getUri()->getPath());
        log_message('debug', '- Method: ' . $request->getMethod());
        log_message('debug', 'Session Details:');
        log_message('debug', '- Session ID: ' . session_id());
        log_message('debug', '- Session Data: ' . json_encode($session->get()));

        // First check if user is logged in
        if (!$session->get('logged_in')) {
            log_message('error', 'User not logged in, redirecting to login');
            return redirect()->to(base_url('auth/login'))
                ->with('error', 'Please login to continue.');
        }

        // Then check if user is admin
        if (!$session->get('is_admin')) {
            log_message('error', 'Access denied: User is not an admin');
            log_message('error', 'User Details:');
            log_message('error', '- User ID: ' . $session->get('pegawai_id'));
            log_message('error', '- Username: ' . $session->get('username'));
            log_message('error', '- Name: ' . $session->get('nama'));
            
            // If AJAX request, return JSON response
            if ($request->hasHeader('X-Requested-With') && 
                strtolower($request->header('X-Requested-With')->getValue()) === 'xmlhttprequest') {
                return service('response')
                    ->setStatusCode(403)
                    ->setJSON([
                        'success' => false,
                        'message' => 'Access denied. Admin privileges required.',
                        'redirect' => base_url('meeting/calendar')
                    ]);
            }

            // For regular requests, redirect to calendar with error message
            return redirect()->to(base_url('meeting/calendar'))
                ->with('error', 'Access denied. Admin privileges required.');
        }

        // Admin user, proceed with request
        log_message('debug', 'Admin access granted');
        log_message('debug', 'Admin Details:');
        log_message('debug', '- User ID: ' . $session->get('pegawai_id'));
        log_message('debug', '- Username: ' . $session->get('username'));
        log_message('debug', '- Name: ' . $session->get('nama'));
        
        return;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Log response status
        log_message('debug', '=== AdminFilter: After Request ===');
        log_message('debug', 'Response Status: ' . $response->getStatusCode());
        
        // If there was a redirect, log it
        if ($response->hasHeader('Location')) {
            log_message('debug', 'Redirect Location: ' . $response->header('Location')->getValue());
        }
        
        // If access was denied, log it
        if ($response->getStatusCode() === 403) {
            log_message('error', 'Access denied response sent');
        }
        
        return;
    }
}
