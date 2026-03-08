<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'userId',
        'schoolId',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }

    public function school()
    {
        return $this->belongsTo(School::class, 'schoolId');
    }
}