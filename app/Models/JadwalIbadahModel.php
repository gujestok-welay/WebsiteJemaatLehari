<?php

namespace App\Models;

use CodeIgniter\Model;

class JadwalIbadahModel extends Model
{
    protected $table = 'jadwal_ibadah';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';
    protected $allowedFields = ['jenis_ibadah', 'tanggal', 'waktu', 'lokasi', 'pelayan', 'is_cancelled', 'created_by', 'updated_by'];

    protected $validationRules = [
        'jenis_ibadah' => 'required|min_length[2]|max_length[120]',
        'tanggal' => 'required|valid_date',
        'waktu' => 'required',
        'lokasi' => 'required|min_length[3]|max_length[255]',
        'pelayan' => 'permit_empty|max_length[255]',
        'is_cancelled' => 'permit_empty|in_list[0,1]',
    ];

    public function findByTerm(?string $term = null, ?int $cancelled = null, bool $archived = false)
    {
        $builder = $this->builder();
        if ($archived) {
            $builder->where($this->deletedField . ' IS NOT NULL', null, false);
        } else {
            $builder->where($this->deletedField, null);
        }

        if (! empty($term)) {
            $builder->groupStart()
                ->like('jenis_ibadah', trim($term))
                ->orLike('lokasi', trim($term))
                ->orLike('pelayan', trim($term))
                ->groupEnd();
        }

        if ($cancelled !== null) {
            $builder->where('is_cancelled', $cancelled);
        }

        return $builder->orderBy('tanggal', 'ASC')->orderBy('waktu', 'ASC')->get()->getResultArray();
    }

    public function findPublicSchedules(): array
    {
        return $this->builder()
            ->where($this->deletedField, null)
            ->where('is_cancelled', 0)
            ->orderBy('tanggal', 'ASC')
            ->orderBy('waktu', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function findArchivedById(int $id): ?array
    {
        return $this->builder()
            ->where($this->primaryKey, $id)
            ->where($this->deletedField . ' IS NOT NULL', null, false)
            ->get()
            ->getRowArray();
    }

    public function restoreArchived(int $id): bool
    {
        return $this->builder()
            ->where($this->primaryKey, $id)
            ->where($this->deletedField . ' IS NOT NULL', null, false)
            ->update([
                $this->deletedField => null,
                $this->updatedField => date('Y-m-d H:i:s'),
            ]);
    }
}
