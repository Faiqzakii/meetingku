<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Rate limiter filter using CI4's built-in Throttler service.
 * 60 requests per minute per IP.
 */
class ThrottleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $throttler = service('throttler');
        $ip = $request->getIPAddress();

        // 60 requests per minute per IP
        if ($throttler->check('api_' . md5($ip), 60, MINUTE) === false) {
            return service('response')
                ->setStatusCode(429)
                ->setJSON([
                    'error' => 'Too many requests. Try again later.',
                ]);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No-op
    }
}
