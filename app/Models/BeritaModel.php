<?php

namespace App\Models;

use CodeIgniter\Model;

class BeritaModel extends Model
{
    protected $table = 'berita';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';
    protected $allowedFields = ['title', 'slug', 'summary', 'content', 'status', 'cover_image', 'created_by', 'updated_by'];

    protected $validationRules = [
        'title' => 'required|min_length[3]|max_length[255]',
        'slug' => 'required|max_length[255]|is_unique[berita.slug,berita.id,{id}]',
        'summary' => 'required|max_length[500]',
        'content' => 'required',
        'status' => 'required|in_list[Draft,Terbit,Arsip]',
    ];

    public function findByTerm(?string $term = null, ?string $status = null)
    {
        $builder = $this->builder();

        if (! empty($term)) {
            $builder->like('title', trim($term));
        }

        if (! empty($status) && $status !== 'all') {
            $builder->where('status', $status);
        }

        return $builder->orderBy('created_at', 'DESC')->get()->getResultArray();
    }

    public function findPublishedByTerm(?string $term = null): array
    {
        $builder = $this->where('status', 'Terbit');

        if (! empty($term)) {
            $builder->groupStart()
                ->like('title', trim($term))
                ->orLike('summary', trim($term))
                ->orLike('content', trim($term))
                ->groupEnd();
        }

        return $builder->orderBy('created_at', 'DESC')->findAll();
    }

    public static function statusOptions(): array
    {
        return ['Draft', 'Terbit', 'Arsip'];
    }

    public static function generateSlug(string $title): string
    {
        $slug = strtolower(trim($title));
        $slug = preg_replace('/[^a-z0-9\s-]+/', '', $slug) ?? $slug;
        $slug = preg_replace('/\s+/', '-', $slug) ?? $slug;
        $slug = preg_replace('/-+/', '-', trim($slug, '-')) ?? $slug;

        return $slug ?: 'berita-baru';
    }
}
