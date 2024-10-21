<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'faculty_id',
        'deadline',
        'hk_type',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function faculty()
    {
        return $this->belongsTo(User::class, 'faculty_id');
    }
}
