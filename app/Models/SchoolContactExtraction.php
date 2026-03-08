<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolContactExtraction extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'school_contact_extractions';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'schoolId',
        'sourceId',
        'foundEmails',
        'foundPhones',
        'foundAddresses',
        'foundSocialLinks',
        'pages',
        'approvedBy',
        'approvedAt',
    ];

    protected function casts(): array
    {
        return [
            'foundEmails' => 'array',
            'foundPhones' => 'array',
            'foundAddresses' => 'array',
            'foundSocialLinks' => 'array',
            'pages' => 'array',
            'approvedAt' => 'datetime',
        ];
    }

    public function school()
    {
        return $this->belongsTo(School::class, 'schoolId');
    }

    public function source()
    {
        return $this->belongsTo(SchoolContactSource::class, 'sourceId');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approvedBy');
    }
}