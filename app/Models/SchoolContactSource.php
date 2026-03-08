<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolContactSource extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'school_contact_sources';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'schoolId',
        'sourceUrl',
        'domain',
        'status',
        'lastScrapedAt',
        'lastError',
        'pagesScraped',
    ];

    protected function casts(): array
    {
        return [
            'lastScrapedAt' => 'datetime',
            'pagesScraped' => 'integer',
        ];
    }

    public function school()
    {
        return $this->belongsTo(School::class, 'schoolId');
    }

    public function extractions()
    {
        return $this->hasMany(SchoolContactExtraction::class, 'sourceId');
    }
}