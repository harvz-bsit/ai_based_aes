<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlliedCourse extends Model
{
    use HasFactory;

    protected $table = 'allied_courses';

    protected $fillable = [
        'course',
        'allied',
    ];

    protected $casts = [
        'allied' => 'array', // Automatically convert JSON <-> array
    ];

    public function jobVacancies()
    {
        return $this->hasMany(JobVacancy::class, 'course', 'course');
    }
}
