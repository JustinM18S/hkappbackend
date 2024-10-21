<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentTask extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'student_id',
        'task',
        'assigned_room',
        'duty_date',
        'duty_start',
        'duty_end',
        'status',
    ];

    /**
     * Define a relationship to the User model (Student).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function getTaskDurationInHours()
    {
        $start = new \DateTime($this->duty_start);
        $end = new \DateTime($this->duty_end);
        $duration = $start->diff($end);

        return ($duration->h) + ($duration->i / 60); 
    }

}
