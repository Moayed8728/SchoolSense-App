<?php

/*
school_documents
= what we told Gemini

school_embeddings
= what Gemini understood
*/
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class SchoolEmbedding extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'schoolId',
        'documentId',
        'model',
        'dimensions',
        'contentHash',
        'embeddedAt',
    ];

    protected function casts(): array
    {
        return [
            'embeddedAt' => 'datetime',
            'dimensions' => 'integer',
        ];
    }

    public function school()
    {
        return $this->belongsTo(School::class, 'schoolId');
    }

    public function document()
    {
        return $this->belongsTo(SchoolDocument::class, 'documentId');
    }
}