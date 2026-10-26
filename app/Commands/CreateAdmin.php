<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class CreateAdmin extends BaseCommand
{
    protected $group = 'Auth';
    protected $name = 'auth:create-admin';
    protected $description = 'Create or update an admin account using interactive input.';

    public function run(array $params)
    {
        $name = trim((string) CLI::prompt('Nama admin'));
        $email = strtolower(trim((string) CLI::prompt('Email admin')));
        if ($name === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            CLI::error('Nama atau email tidak valid.');
            return EXIT_ERROR;
        }

        $password = CLI::prompt('Kata sandi (min. 12 karakter)', null, 'required');
        if (strlen((string) $password) < 12) {
            CLI::error('Kata sandi harus minimal 12 karakter.');
            return EXIT_ERROR;
        }

        $db = db_connect();
        $existing = $db->table('admin_users')->where('email', $email)->get()->getRowArray();
        $data = ['name' => $name, 'email' => $email, 'password_hash' => password_hash((string) $password, PASSWORD_DEFAULT), 'updated_at' => date('Y-m-d H:i:s')];
        if ($existing) {
            $db->table('admin_users')->where('id', $existing['id'])->update($data);
            CLI::write('Akun admin diperbarui.');
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $db->table('admin_users')->insert($data);
            CLI::write('Akun admin dibuat.');
        }
        return EXIT_SUCCESS;
    }
}
