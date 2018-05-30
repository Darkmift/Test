<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    public $table = 'enrollments';
    public $fillable = [
        'student_id',
        'course_id',
        'admin_id',
    ];
}
