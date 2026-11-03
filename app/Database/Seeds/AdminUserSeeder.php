<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        $db = db_connect();

        if (!$db->tableExists('admin_users')) {
            throw new \RuntimeException('Table admin_users does not exist. Run migrations before seeding.');
        }

        $email = 'gujestokjondrywelay@gmai.com';
        $password = 'admin12345678';
        $now = date('Y-m-d H:i:s');

        $existing = $db->table('admin_users')->where('email', $email)->get()->getRowArray();

        $data = [
            'name' => 'Admin',
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'role' => 'super_admin',
            'is_active' => 1,
            'updated_at' => $now,
        ];

        if ($existing) {
            $db->table('admin_users')->where('id', $existing['id'])->update($data);
            return;
        }

        $data['created_at'] = $now;
        $db->table('admin_users')->insert($data);
    }
}
