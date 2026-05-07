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

class SchoolDocument extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'schoolId',
        'content',
        'contentHash',
        'generatedAt',
    ];

    protected function casts(): array
    {
        return [
            'generatedAt' => 'datetime',
        ];
    }

    public function school()
    {
        return $this->belongsTo(School::class, 'schoolId');
    }
}