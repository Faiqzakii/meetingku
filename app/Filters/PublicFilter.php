<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class PublicFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        
        log_message('debug', '=== PublicFilter: Check Started ===');
        log_message('debug', 'Request Details:');
        log_message('debug', '- URI: ' . $request->getUri()->getPath());
        log_message('debug', '- Method: ' . $request->getMethod());
        log_message('debug', 'Session Details:');
        log_message('debug', '- Session ID: ' . session_id());
        log_message('debug', '- Session Data: ' . json_encode($session->get()));

        // If user is already logged in, redirect to calendar
        // except for logout route
        if ($session->get('logged_in')) {
            $currentPath = $request->getUri()->getPath();
            
            if ($currentPath !== 'auth/logout' && !str_contains($currentPath, 'logout')) {
                log_message('debug', 'User already logged in, redirecting to calendar');
                log_message('debug', 'User Details:');
                log_message('debug', '- User ID: ' . $session->get('pegawai_id'));
                log_message('debug', '- Username: ' . $session->get('username'));
                log_message('debug', '- Name: ' . $session->get('nama'));
                log_message('debug', '- Is Admin: ' . ($session->get('is_admin') ? 'yes' : 'no'));

                return redirect()->to(base_url('meeting/calendar'));
            }
        }

        log_message('debug', 'User not logged in, allowing access to public route');
        return;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Log response status
        log_message('debug', '=== PublicFilter: After Request ===');
        log_message('debug', 'Response Status: ' . $response->getStatusCode());
        
        // If there was a redirect, log it
        if ($response->hasHeader('Location')) {
            log_message('debug', 'Redirect Location: ' . $response->header('Location')->getValue());
        }
        
        return;
    }
}
