<?php

namespace App\Models;

use CodeIgniter\Model;

class KegiatanModel extends Model
{
    protected $table = 'kegiatan';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $allowedFields = [
        'title',
        'slug',
        'description',
        'event_date',
        'video_url',
        'poster_path',
        'poster_type',
        'status',
        'show_on_home',
        'archived_from_status',
        'created_by',
        'updated_by',
    ];

    protected $validationRules = [
        'id' => 'permit_empty|integer',
        'title' => 'required|min_length[3]|max_length[255]',
        'slug' => 'required|max_length[255]|is_unique[kegiatan.slug,kegiatan.id,{id}]',
        'description' => 'permit_empty|max_length[5000]',
        'event_date' => 'permit_empty|valid_date[Y-m-d]',
        'video_url' => 'permit_empty|max_length[2048]',
        'poster_path' => 'required|max_length[255]',
        'poster_type' => 'required|in_list[poster,flayer,spanduk]',
        'status' => 'required|in_list[Draft,Terbit,Arsip]',
        'show_on_home' => 'permit_empty|in_list[0,1]',
        'archived_from_status' => 'permit_empty|in_list[Draft,Terbit]',
    ];

    public static function statusOptions(): array
    {
        return ['Draft', 'Terbit', 'Arsip'];
    }

    public static function formStatusOptions(): array
    {
        return ['Draft', 'Terbit'];
    }

    public static function mediaTypeOptions(): array
    {
        return [
            'foto' => 'Foto',
            'poster' => 'Poster',
            'flayer' => 'Flayer',
            'spanduk' => 'Spanduk',
        ];
    }

    public static function posterTypeOptions(): array
    {
        return [
            'poster' => 'Poster',
            'flayer' => 'Flayer',
            'spanduk' => 'Spanduk',
        ];
    }

    public function update($id = null, $row = null): bool
    {
        if ((is_int($id) || is_string($id)) && is_array($row)) {
            $row['id'] = $id;
        }

        return parent::update($id, $row);
    }

    public function findPublished(): array
    {
        return $this->where('status', 'Terbit')
            ->orderBy('event_date', 'DESC')
            ->orderBy('id', 'DESC')
            ->findAll();
    }

    public function findFeaturedForHome(int $limit = 3): array
    {
        return $this->where('status', 'Terbit')
            ->where('show_on_home', 1)
            ->orderBy('event_date', 'DESC')
            ->orderBy('id', 'DESC')
            ->findAll(max(1, min($limit, 6)));
    }

    public function findPublishedBySlug(string $slug): ?array
    {
        $record = $this->where('status', 'Terbit')->where('slug', $slug)->first();

        return is_array($record) ? $record : null;
    }

    public function findByTerm(?string $term = null, ?string $status = null): array
    {
        $builder = $this->builder();

        if ($term !== null && trim($term) !== '') {
            $builder->groupStart()
                ->like('title', trim($term))
                ->orLike('description', trim($term))
                ->groupEnd();
        }

        if ($status !== null && in_array($status, self::statusOptions(), true)) {
            $builder->where('status', $status);
        }

        return $builder->orderBy('updated_at', 'DESC')->orderBy('id', 'DESC')->get()->getResultArray();
    }

    public function generateSlug(string $title, int $excludeId = 0): string
    {
        $base = strtolower(trim($title));
        $base = preg_replace('/[^a-z0-9\s-]+/', '', $base) ?? $base;
        $base = preg_replace('/\s+/', '-', $base) ?? $base;
        $base = preg_replace('/-+/', '-', trim($base, '-')) ?? $base;
        $base = $base !== '' ? $base : 'kegiatan';
        $slug = $base;
        $suffix = 2;

        while ($this->where('slug', $slug)->where('id !=', $excludeId)->countAllResults() > 0) {
            $slug = $base . '-' . $suffix;
            $suffix++;
        }

        return $slug;
    }
}
