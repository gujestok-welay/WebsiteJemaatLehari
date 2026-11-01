<?php

namespace App\Models;

use CodeIgniter\Model;

class AdminUserModel extends Model
{
    protected $table = 'admin_users';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';
    protected $allowedFields = [
        'name',
        'email',
        'password_hash',
        'role',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $validationRules = [
        'name' => 'required|min_length[2]|max_length[120]',
        'email' => 'required|valid_email|max_length[191]|is_unique[admin_users.email,admin_users.id,{id}]',
        'password_hash' => 'required|max_length[255]',
        'role' => 'required|in_list[super_admin,operator]',
        'is_active' => 'permit_empty|in_list[0,1]',
    ];

    public static function getRoleOptions(): array
    {
        return ['super_admin', 'operator'];
    }

    public static function isSuperAdminRole(?string $role): bool
    {
        return $role === 'super_admin';
    }
}
