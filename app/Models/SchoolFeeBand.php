<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolFeeBand extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'school_fee_bands';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'schoolId',
        'gradeFrom',
        'gradeTo',
        'gradeFromOrder',
        'gradeToOrder',
        'feesMin',
        'feesMax',
        'currency',
        'feePeriod',
    ];

    protected function casts(): array
    {
        return [
            'gradeFromOrder' => 'integer',
            'gradeToOrder' => 'integer',
            'feesMin' => 'integer',
            'feesMax' => 'integer',
        ];
    }

    public function school()
    {
        return $this->belongsTo(School::class, 'schoolId');
    }
}   