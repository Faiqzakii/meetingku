<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var array
     */
    protected $helpers = ['form', 'url'];

    /**
     * Session instance
     */
    protected $session;

    /**
     * Constructor.
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.
        $this->session = \Config\Services::session();
    }

    /**
     * Helper method to check if user is logged in
     */
    protected function isLoggedIn(): bool
    {
        return (bool) $this->session->get('logged_in');
    }

    /**
     * Helper method to check if user is admin
     */
    protected function isAdmin(): bool
    {
        return (bool) $this->session->get('is_admin');
    }

    /**
     * Helper method to get current user ID
     */
    protected function getCurrentUserId(): ?int
    {
        return $this->session->get('pegawai_id');
    }

    /**
     * Helper method to get current user data
     */
    protected function getCurrentUser(): ?array
    {
        if (!$this->isLoggedIn()) {
            return null;
        }

        return [
            'id' => $this->session->get('pegawai_id'),
            'username' => $this->session->get('username'),
            'nama' => $this->session->get('nama'),
            'is_admin' => $this->session->get('is_admin')
        ];
    }
}
