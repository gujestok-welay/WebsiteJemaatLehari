<?php

namespace App\Models;

use CodeIgniter\Model;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Database\ResultInterface;

class BeritaModel extends Model
{
    protected $table = 'berita';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $allowedFields = [
        'title',
        'slug',
        'summary',
        'content',
        'status',
        'archived_from_status',
        'cover_image',
        'article_type',
        'source_url',
        'source_name',
        'created_by',
        'updated_by',
    ];

    protected $validationRules = [
        'id' => 'permit_empty|integer',
        'title' => 'required|min_length[3]|max_length[255]',
        'slug' => 'required|max_length[255]|is_unique[berita.slug,berita.id,{id}]',
        'summary' => 'required|max_length[500]',
        'content' => 'required',
        'status' => 'required|in_list[Draft,Terbit,Arsip]',
        'archived_from_status' => 'permit_empty|in_list[Draft,Terbit]',
        'article_type' => 'permit_empty|in_list[internal,external]',
        'source_url' => 'permit_empty|max_length[2048]',
        'source_name' => 'permit_empty|max_length[255]',
    ];

    /**
     * Reads one record while distinguishing a failed query from an empty result.
     *
     * @return array{status: 'found', record: array|object}|array{status: 'not_found'|'error', record: null}
     */
    public function findForAdmin(int $id): array
    {
        if ($this->tempAllowCallbacks) {
            $eventData = $this->trigger('beforeFind', [
                'id'        => $id,
                'method'    => 'find',
                'singleton' => true,
            ]);

            if (($eventData['returnData'] ?? false) === true) {
                return $this->adminLookupResult($eventData['data'] ?? null);
            }
        }

        $returnType = $this->tempReturnType;
        $useCast    = $this->useCasts();
        if ($useCast) {
            $this->asArray();
        }

        try {
            $builder = $this->builder();

            if ($this->tempUseSoftDeletes) {
                $builder->where($this->table . '.' . $this->deletedField, null);
            }

            $query = $builder->where($this->table . '.' . $this->primaryKey, $id)->get();
            if (! ($query instanceof ResultInterface)) {
                $this->resetAdminLookupState();

                return $this->adminLookupResult(null, 'error');
            }

            $record = $query->getFirstRow($this->tempReturnType);

            $eventData = [
                'id'        => $id,
                'data'      => $record,
                'method'    => 'find',
                'singleton' => true,
            ];

            if ($this->tempAllowCallbacks) {
                $eventData = $this->trigger('afterFind', $eventData);
                $record    = $eventData['data'];
            }
        } catch (DatabaseException) {
            $this->resetAdminLookupState();

            return $this->adminLookupResult(null, 'error');
        }

        $this->resetAdminLookupState();

        if ($useCast) {
            if ($record === null) {
                return $this->adminLookupResult(null);
            }

            $record = $this->convertToReturnType($record, $returnType);
        }

        return $this->adminLookupResult($record);
    }

    public function update($id = null, $row = null): bool
    {
        $updated = parent::update($id, $row);
        if (! $updated || (! is_int($id) && ! is_string($id))) {
            return $updated;
        }

        $id = (string) $id;
        if ($id === '' || ! ctype_digit($id) || (int) $id < 1 || $this->db->affectedRows() !== 0) {
            return $updated;
        }

        return $this->findForAdmin((int) $id)['status'] !== 'not_found';
    }

    /**
     * @param array|object|null $record
     * @param 'found'|'not_found'|'error'|null $status
     *
     * @return array{status: 'found', record: array|object}|array{status: 'not_found'|'error', record: null}
     */
    private function adminLookupResult(array|object|null $record, ?string $status = null): array
    {
        $status ??= $record === null ? 'not_found' : 'found';

        return [
            'status' => $status,
            'record' => $status === 'found' ? $record : null,
        ];
    }

    private function resetAdminLookupState(): void
    {
        $this->tempReturnType     = $this->returnType;
        $this->tempUseSoftDeletes = $this->useSoftDeletes;
        $this->tempAllowCallbacks = $this->allowCallbacks;
    }

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

        return $builder
            ->orderBy('updated_at', 'DESC')
            ->orderBy('created_at', 'DESC')
            ->orderBy('id', 'DESC')
            ->findAll();
    }

    public static function statusOptions(): array
    {
        return ['Draft', 'Terbit', 'Arsip'];
    }

    public static function formStatusOptions(): array
    {
        return ['Draft', 'Terbit'];
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
