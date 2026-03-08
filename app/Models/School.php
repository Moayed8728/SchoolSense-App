<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class School extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
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
        'ownerUserId'
    ];

    protected function casts(): array
    {
        return [
            'feesMin' => 'integer',
            'feesMax' => 'integer',
            'deleted_at' => 'datetime',
        ];
    }

    public function curricula()
    {
        return $this->belongsToMany(Curriculum::class, 'curriculum_school', 'schoolId', 'curriculumId')
            ->withTimestamps();
    }

    public function activities()
    {
        return $this->belongsToMany(Activity::class, 'activity_school', 'schoolId', 'activityId')
            ->withTimestamps();
    }

    public function languages()
    {
        return $this->belongsToMany(Language::class, 'language_school', 'schoolId', 'languageId')
            ->withTimestamps();
    }

    public function feeBands()
    {
        return $this->hasMany(SchoolFeeBand::class, 'schoolId');
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class, 'schoolId');
    }

    public function contactSources()
    {
        return $this->hasMany(SchoolContactSource::class, 'schoolId');
    }

    public function contactExtractions()
    {
        return $this->hasMany(SchoolContactExtraction::class, 'schoolId');
    }
    
    public function favoritedByUsers()
{
    return $this->belongsToMany(User::class, 'favorites', 'schoolId', 'userId')
        ->withTimestamps();
}

public function owner()
{
    return $this->belongsTo(User::class, 'ownerUserId');
}

}