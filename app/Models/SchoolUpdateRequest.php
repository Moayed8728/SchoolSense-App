<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SchoolUpdateRequest extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'userId',
        'schoolId',
        'type',
        'changes',
        'status',
        'adminReason',
        'reviewedBy',
        'reviewedAt',
    ];

    protected function casts(): array
    {
        return [
            'changes' => 'array',
            'reviewedAt' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }

    public function school()
    {
        return $this->belongsTo(School::class, 'schoolId');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewedBy');
    }
}
