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

        // First check if user is logged in
        if (!$session->get('logged_in')) {
            log_message('warning', 'Admin route accessed without login: ' . $request->getUri()->getPath());
            return redirect()->to(base_url('auth/login'))
                ->with('error', 'Please login to continue.');
        }

        // Then check if user is admin
        if (!$session->get('is_admin')) {
            log_message('warning', 'Admin access denied for user ID: ' . (string) $session->get('pegawai_id'));
            
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

        return;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // If access was denied, log it
        if ($response->getStatusCode() === 403) {
            log_message('warning', 'Admin filter returned 403 for: ' . $request->getUri()->getPath());
        }
        
        return;
    }
}
