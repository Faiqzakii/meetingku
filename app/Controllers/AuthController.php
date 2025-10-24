<?php

namespace App\Controllers;

use App\Models\PegawaiModel;
use CodeIgniter\Controller;

class AuthController extends Controller
{
    protected $pegawaiModel;

    public function __construct()
    {
        $this->pegawaiModel = new PegawaiModel();
    }

    public function login()
    {
        if (session()->get('logged_in')) {
            return redirect()->to('/meeting/calendar');
        }
        return view('auth/login');
    }

    public function attemptLogin()
    {
        // Validation rules
        $rules = [
            'username' => [
                'rules' => 'required|min_length[3]|max_length[50]',
                'errors' => [
                    'required' => 'Username harus diisi',
                    'min_length' => 'Username minimal 3 karakter',
                    'max_length' => 'Username maksimal 50 karakter'
                ]
            ],
            'password' => [
                'rules' => 'required|min_length[3]',
                'errors' => [
                    'required' => 'Password harus diisi',
                    'min_length' => 'Password minimal 3 karakter'
                ]
            ]
        ];

        // Run validation
        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('error', $this->validator->listErrors());
        }

        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        // Check if username exists
        $pegawai = $this->pegawaiModel->where('username', $username)->first();
        
        if (!$pegawai) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Username tidak ditemukan');
        }

        if ($pegawai && password_verify($password, $pegawai['password'])) {
            $sessionData = [
                'pegawai_id' => $pegawai['id'],
                'nama' => $pegawai['nama'],
                'username' => $pegawai['username'],
                'is_admin' => (bool)$pegawai['is_admin'],
                'logged_in' => true
            ];

            session()->set($sessionData);
            return redirect()->to('/')->with('success', 'Login berhasil');
        }

        return redirect()->back()
            ->with('error', 'Username atau password salah')
            ->withInput();
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/')->with('success', 'Logout berhasil');
    }
}
