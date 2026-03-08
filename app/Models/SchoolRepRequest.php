<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SchoolRepRequest extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'userId',
        'fullName',
        'schoolName',
        'websiteUrl',
        'proofText',
        'status',
        'adminReason',
        'reviewedBy',
        'reviewedAt',
    ];

    protected function casts(): array
    {
        return [
            'reviewedAt' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewedBy');
    }
}