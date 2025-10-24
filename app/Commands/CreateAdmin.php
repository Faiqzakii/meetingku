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

        // Get username
        $username = CLI::prompt('Enter username (3-25 characters)', null, 'required|min_length[3]|max_length[25]');
        
        // Check if username exists
        if ($pegawaiModel->where('username', $username)->first()) {
            CLI::error('Username already exists');
            return;
        }

        // Get NIP
        $nip = CLI::prompt('Enter NIP (18 digits)', null, 'required|exact_length[18]|numeric');
        
        // Check if NIP exists
        if ($pegawaiModel->where('nip', $nip)->first()) {
            CLI::error('NIP already exists');
            return;
        }

        // Get password
        $password = CLI::prompt('Enter password (min 6 characters)', null, 'required|min_length[6]');
        
        // Get name
        $nama = CLI::prompt('Enter full name (3-100 characters)', null, 'required|min_length[3]|max_length[100]');

        // Create admin user
        $data = [
            'username' => $username,
            'password' => $password,
            'nama'     => $nama,
            'nip'      => $nip,
            'is_admin' => 1
        ];

        try {
            log_message('debug', 'Creating admin user via CLI command');
            log_message('debug', '- Username: ' . $username);
            log_message('debug', '- Name: ' . $nama);
            log_message('debug', '- NIP: ' . $nip);

            if ($pegawaiModel->createAdmin($data)) {
                CLI::write('Admin user created successfully', 'green');
                
                // Show created user details
                $user = $pegawaiModel->where('username', $username)->first();
                CLI::write('User Details:', 'yellow');
                CLI::write("ID: {$user['id']}", 'white');
                CLI::write("Username: {$user['username']}", 'white');
                CLI::write("Name: {$user['nama']}", 'white');
                CLI::write("NIP: {$user['nip']}", 'white');
                CLI::write("Is Admin: Yes", 'white');
                
                // Show login instructions
                CLI::newLine();
                CLI::write('Login Instructions:', 'yellow');
                CLI::write("1. Go to: " . site_url('auth/login'), 'white');
                CLI::write("2. Username: {$user['username']}", 'white');
                CLI::write("3. Password: [The password you entered]", 'white');
                
                log_message('info', 'Admin user created successfully via CLI');
                log_message('debug', 'Created user details: ' . json_encode($user));
            } else {
                CLI::error('Failed to create admin user');
                CLI::error('Validation errors:');
                foreach ($pegawaiModel->errors() as $error) {
                    CLI::error('- ' . $error);
                }
                
                log_message('error', 'Failed to create admin user via CLI');
                log_message('error', 'Validation errors: ' . json_encode($pegawaiModel->errors()));
            }
        } catch (\Exception $e) {
            CLI::error('Error creating admin user: ' . $e->getMessage());
            log_message('error', 'Exception while creating admin user: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
        }
    }
}
