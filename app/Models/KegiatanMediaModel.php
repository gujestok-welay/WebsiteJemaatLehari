<?php

namespace App\Models;

use CodeIgniter\Model;

class KegiatanMediaModel extends Model
{
    protected $table = 'kegiatan_media';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = ['kegiatan_id', 'media_type', 'file_path'];

    protected $validationRules = [
        'kegiatan_id' => 'required|integer',
        'media_type' => 'required|in_list[foto,poster,flayer,spanduk]',
        'file_path' => 'required|max_length[255]',
    ];

    public function findByKegiatan(int $kegiatanId): array
    {
        return $this->where('kegiatan_id', $kegiatanId)->orderBy('id', 'ASC')->findAll();
    }

    public function findPublishedByPath(string $path): ?array
    {
        $record = $this->select('kegiatan_media.*')
            ->join('kegiatan', 'kegiatan.id = kegiatan_media.kegiatan_id')
            ->where('kegiatan.status', 'Terbit')
            ->where('kegiatan_media.file_path', $path)
            ->first();

        return is_array($record) ? $record : null;
    }
}
