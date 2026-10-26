<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAdminAuthTables extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 120],
            'email' => ['type' => 'VARCHAR', 'constraint' => 191],
            'password_hash' => ['type' => 'VARCHAR', 'constraint' => 255],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('email');
        $this->forge->createTable('admin_users', true);

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'identity_hash' => ['type' => 'CHAR', 'constraint' => 64],
            'attempted_at' => ['type' => 'INT', 'unsigned' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['identity_hash', 'attempted_at']);
        $this->forge->createTable('admin_login_attempts', true);
    }

    public function down()
    {
        // Intentionally no destructive rollback; preserve authentication data.
    }
}
