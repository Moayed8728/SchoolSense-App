<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Curriculum extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'slug',
    ];

    public function schools()
    {
        return $this->belongsToMany(School::class, 'curriculum_school', 'curriculumId', 'schoolId')
            ->withTimestamps();
    }
}