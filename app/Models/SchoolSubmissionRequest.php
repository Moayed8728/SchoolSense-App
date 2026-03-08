<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SchoolSubmissionRequest extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'userId',
        'name','country','city','address',
        'websiteUrl','contactEmail','contactPhone','contactPageUrl',
        'description',
        'feesMin','feesMax','currency','feePeriod',
        'curriculumIds','activityIds','languageIds',
        'status','adminReason',
        'reviewedBy','reviewedAt',
    ];

    protected function casts(): array
    {
        return [
            'feesMin' => 'integer',
            'feesMax' => 'integer',
            'curriculumIds' => 'array',
            'activityIds' => 'array',
            'languageIds' => 'array',
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