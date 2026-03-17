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

        // If user is already logged in, redirect to calendar
        // except for logout route
        if ($session->get('logged_in')) {
            $currentPath = $request->getUri()->getPath();
            
            if ($currentPath !== 'auth/logout' && !str_contains($currentPath, 'logout')) {
                return redirect()->to(base_url('meeting/calendar'));
            }
        }
        return;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return;
    }
}
