<?php

namespace App\Models;

use CodeIgniter\Model;

class ProfilJemaatModel extends Model
{
    protected $table = 'profil_jemaat';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';
    protected $allowedFields = [
        'history',
        'vision_mission',
        'address',
        'maps_url',
        'contact_info',
        'page_content',
        'territory_content',
        'census_content',
        'service_map_path',
        'service_map_title',
        'service_map_alt',
        'created_by',
        'updated_by',
    ];

    protected $validationRules = [
        'history' => 'required|min_length[20]',
        'vision_mission' => 'required|min_length[20]',
        'address' => 'required|min_length[10]',
        'maps_url' => 'permit_empty|valid_url',
        'contact_info' => 'required|min_length[10]',
    ];
}
