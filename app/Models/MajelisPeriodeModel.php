<?php

namespace App\Models;

use CodeIgniter\Model;

class MajelisPeriodeModel extends Model
{
    protected $table = 'majelis_periode';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';
    protected $allowedFields = [
        'period_name',
        'start_date',
        'end_date',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $validationRules = [
        'period_name' => 'required|min_length[3]|max_length[120]',
        'start_date' => 'required|valid_date',
        'end_date' => 'permit_empty|valid_date',
        'is_active' => 'permit_empty|in_list[0,1]',
    ];

    public function setActivePeriod(int $id, int $adminId): bool
    {
        $this->db->table($this->table)->update(['is_active' => 0]);
        return (bool) $this->update($id, ['is_active' => 1, 'updated_by' => $adminId]);
    }
}
