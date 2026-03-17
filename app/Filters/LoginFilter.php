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

        // Check if user is logged in
        if (!$session->get('logged_in')) {
            log_message('info', 'Unauthenticated request to: ' . $request->getUri()->getPath());

            // Store the current URL for redirect after login
            $currentURL = current_url();
            $loginPath = 'auth/login';
            
            // Only store redirect if not already on login page
            if (!str_contains($currentURL, $loginPath)) {
                $session->set('redirect_url', $currentURL);
            }

            // If AJAX request, return JSON response
            if ($this->isAjaxRequest($request)) {
                return service('response')
                    ->setStatusCode(401)
                    ->setJSON([
                        'success' => false,
                        'message' => 'Session expired. Please login again.',
                        'redirect' => base_url('auth/login')
                    ]);
            }

            // For regular requests, redirect to login page
            return redirect()->to(base_url('auth/login'))
                ->with('error', 'Please login to continue.');
        }

        // Check if session is about to expire
        $sessionExpiry = $session->get('__ci_last_regenerate') + (config('Session')->expiration ?? 7200);
        $timeLeft = $sessionExpiry - time();
        
        // If session is close to expiring (within 5 minutes), regenerate it
        if ($timeLeft < 300) {
            $session->regenerate(true);
        }
        
        return;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Check if response indicates session timeout
        if ($response->getStatusCode() === 401) {
            session()->destroy();
        }
        
        return;
    }
}
