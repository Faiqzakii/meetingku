<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\PegawaiModel;

class CreateAdmin extends BaseCommand
{
    protected $group       = 'Auth';
    protected $name       = 'auth:create-admin';
    protected $description = 'Creates a new admin user';

    public function run(array $params)
    {
        $pegawaiModel = new PegawaiModel();

        $username = CLI::prompt('Enter username (3-25 characters)', null, 'required|min_length[3]|max_length[25]');
        if ($pegawaiModel->where('username', $username)->first()) {
            CLI::error('Username already exists');
            return;
        }

        $nip = CLI::prompt('Enter NIP (18 digits)', null, 'required|exact_length[18]|numeric');
        if ($pegawaiModel->where('nip', $nip)->first()) {
            CLI::error('NIP already exists');
            return;
        }

        $password = CLI::prompt('Enter password (min 6 characters)', null, 'required|min_length[6]');
        $nama = CLI::prompt('Enter full name (3-100 characters)', null, 'required|min_length[3]|max_length[100]');

        $data = [
            'username' => $username,
            'password' => $password,
            'nama'     => $nama,
            'nip'      => $nip,
            'is_admin' => true
        ];

        try {
            $created = $pegawaiModel->insert($data);
            if ($created !== false) {
                CLI::write('Admin user created successfully', 'green');
                $user = $pegawaiModel->where('username', $username)->first();
                CLI::write('User Details:', 'yellow');
                CLI::write("ID: {$user['id']}", 'white');
                CLI::write("Username: {$user['username']}", 'white');
                CLI::write("Name: {$user['nama']}", 'white');
                CLI::write("NIP: {$user['nip']}", 'white');
                CLI::write("Is Admin: Yes", 'white');
                CLI::newLine();
                CLI::write('Login Instructions:', 'yellow');
                CLI::write("1. Go to: " . site_url('auth/login'), 'white');
                CLI::write("2. Username: {$user['username']}", 'white');
                CLI::write("3. Password: [The password you entered]", 'white');
            } else {
                CLI::error('Failed to create admin user');
                CLI::error('Validation errors:');
                foreach ($pegawaiModel->errors() as $error) {
                    CLI::error('- ' . $error);
                }
            }
        } catch (\Exception $e) {
            CLI::error('Error creating admin user: ' . $e->getMessage());
        }
    }
}
