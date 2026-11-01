<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAdminUserRoles extends Migration
{
    public function up()
    {
        $forge = $this->forge;
        $db = \Config\Database::connect();

        if (! $db->fieldExists('role', 'admin_users')) {
            $forge->addColumn('admin_users', [
                'role' => ['type' => 'VARCHAR', 'constraint' => 32, 'default' => 'operator', 'after' => 'password_hash'],
                'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1, 'after' => 'role'],
                'deleted_at' => ['type' => 'DATETIME', 'null' => true, 'after' => 'is_active'],
                'created_by' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true, 'after' => 'deleted_at'],
                'updated_by' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true, 'after' => 'created_by'],
            ]);
        }

        if ($db->fieldExists('role', 'admin_users')) {
            $db->query('ALTER TABLE `admin_users` MODIFY `role` VARCHAR(32) NOT NULL DEFAULT "operator"');
        }

        if (! $db->fieldExists('is_active', 'admin_users')) {
            $forge->addColumn('admin_users', [
                'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            ]);
        }

        if (! $db->fieldExists('deleted_at', 'admin_users')) {
            $forge->addColumn('admin_users', [
                'deleted_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
        }

        if (! $db->fieldExists('created_by', 'admin_users')) {
            $forge->addColumn('admin_users', [
                'created_by' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            ]);
        }

        if (! $db->fieldExists('updated_by', 'admin_users')) {
            $forge->addColumn('admin_users', [
                'updated_by' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            ]);
        }

        $db->query('UPDATE `admin_users` SET `role` = "super_admin" WHERE `role` IS NULL OR `role` = ""');
    }

    public function down()
    {
        // Intentionally non-destructive; preserves authentication and user records.
    }
}
