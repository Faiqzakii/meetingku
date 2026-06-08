<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $publicRoutes = [
            '',
            'meeting/calendar',
            'meeting/upcoming',
            'auth/login',
            'auth/logout'
        ];

        $currentPath = trim($request->getUri()->getPath(), '/');

        foreach ($publicRoutes as $route) {
            if ($currentPath === $route) {
                return;
            }
        }

        if (!session()->get('logged_in')) {
            return redirect()->to(base_url('auth/login'))->with('error', 'Please login first');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
