<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // List of public routes that don't require login
        $publicRoutes = [
            '',                     // root URL
            'meeting/calendar',     // calendar view
            'meeting/upcoming',     // upcoming meetings
            'auth/login',          // login page
            'auth/logout'          // logout action
        ];

        // Get current path
        $currentPath = trim($request->getUri()->getPath(), '/');

        // Allow access to public routes
        foreach ($publicRoutes as $route) {
            if ($currentPath === $route) {
                return;
            }
        }

        // Require login for all other routes
        if (!session()->get('logged_in')) {
            return redirect()->to(base_url('auth/login'))->with('error', 'Please login first');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
}
