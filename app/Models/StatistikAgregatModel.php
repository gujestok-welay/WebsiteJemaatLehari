<?php

namespace App\Models;

use CodeIgniter\Model;

class StatistikAgregatModel extends Model
{
    protected $table = 'statistik_agregat';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';
    protected $allowedFields = [
        'metric_label',
        'metric_value',
        'unit_label',
        'updated_at',
        'created_by',
        'updated_by',
    ];

    protected $validationRules = [
        'metric_label' => 'required|min_length[2]|max_length[150]',
        'metric_value' => 'required|numeric',
        'unit_label' => 'permit_empty|max_length[80]',
    ];

    public function orderedList(): array
    {
        return $this->orderBy('metric_label', 'ASC')->findAll();
    }
}
