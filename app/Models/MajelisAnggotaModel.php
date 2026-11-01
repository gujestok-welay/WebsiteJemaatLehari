<?php

namespace App\Models;

use CodeIgniter\Model;

class MajelisAnggotaModel extends Model
{
    protected $table = 'majelis_anggota';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';
    protected $allowedFields = [
        'periode_id',
        'name',
        'position',
        'photo_path',
        'created_by',
        'updated_by',
    ];

    protected $validationRules = [
        'periode_id' => 'required|integer',
        'name' => 'required|min_length[2]|max_length[150]',
        'position' => 'required|min_length[2]|max_length[150]',
        'photo_path' => 'permit_empty|max_length[255]',
    ];

    public function findByPeriode(int $periodeId): array
    {
        return $this->where('periode_id', $periodeId)->orderBy('name', 'ASC')->findAll();
    }
}
