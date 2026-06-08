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

        if (!$session->get('logged_in')) {
            log_message('info', 'Unauthenticated request to: ' . $request->getUri()->getPath());

            $currentURL = current_url();
            $loginPath = 'auth/login';
            if (!str_contains($currentURL, $loginPath)) {
                $session->set('redirect_url', $currentURL);
            }

            if ($this->isAjaxRequest($request)) {
                return service('response')
                    ->setStatusCode(401)
                    ->setJSON([
                        'success' => false,
                        'message' => 'Session expired. Please login again.',
                        'redirect' => base_url('auth/login')
                    ]);
            }

            return redirect()->to(base_url('auth/login'))
                ->with('error', 'Please login to continue.');
        }

        $sessionExpiry = $session->get('__ci_last_regenerate') + (config('Session')->expiration ?? 7200);
        $timeLeft = $sessionExpiry - time();
        if ($timeLeft < 300) {
            $session->regenerate(true);
        }

        return;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        if ($response->getStatusCode() === 401) {
            session()->destroy();
        }

        return;
    }
}
