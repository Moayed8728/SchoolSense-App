<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class SchoolManagerApplication extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'fullName',
        'email',
        'password',
        'schoolName',
        'country',
        'city',
        'address',
        'websiteUrl',
        'contactEmail',
        'contactPhone',
        'contactPageUrl',
        'description',
        'feesMin',
        'feesMax',
        'currency',
        'feePeriod',
        'curriculumIds',
        'activityIds',
        'languageIds',
        'proofText',
        'status',
        'adminReason',
        'reviewedBy',
        'reviewedAt',
        'createdUserId',
        'createdSchoolId',
    ];

    protected function casts(): array
    {
        return [
            'curriculumIds' => 'array',
            'activityIds' => 'array',
            'languageIds' => 'array',
            'feesMin' => 'integer',
            'feesMax' => 'integer',
            'reviewedAt' => 'datetime',
        ];
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewedBy');
    }

    public function createdUser()
    {
        return $this->belongsTo(User::class, 'createdUserId');
    }

    public function createdSchool()
    {
        return $this->belongsTo(School::class, 'createdSchoolId');
    }
}
