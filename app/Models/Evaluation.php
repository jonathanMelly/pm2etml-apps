<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'teacher_id',
        'job_definition_id',
        'status',
        'start_date',
        'end_date',
        'student_viewed_at',
        'teacher_viewed_at',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'student_viewed_at' => 'datetime',
        'teacher_viewed_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function jobDefinition()
    {
        return $this->belongsTo(JobDefinition::class);
    }

    public function versions()
    {
        return $this->hasMany(EvaluationVersion::class);
    }

    public function getStudentStatus()
    {
        // Get the absolute latest version using the collection to avoid N+1
        $latestVersion = $this->versions->sortByDesc('created_at')->first();

        if (!$latestVersion) {
            return null;
        }

        // If the latest version is from the TEACHER
        if ($latestVersion->evaluator_type === 'teacher') {
            // It is NEW if not viewed yet or if viewed before this version was created
            if ($this->student_viewed_at === null || $this->student_viewed_at->lt($latestVersion->created_at)) {
                return 'new';
            }
            return 'viewed';
        }

        return null;
    }

    public function getTeacherStatus()
    {
        // Get the absolute latest version
        $latestVersion = $this->versions->sortByDesc('created_at')->first();

        if (!$latestVersion) {
            return null;
        }

        // If the latest version is from the STUDENT
        if ($latestVersion->evaluator_type === 'student') {
            // It is NEW if not viewed yet or if viewed before this version was created
            if ($this->teacher_viewed_at === null || $this->teacher_viewed_at->lt($latestVersion->created_at)) {
                return 'new';
            }
            // If viewed, return null (as per "vu only if teacher saved")
            return null;
        }

        // If the latest version is from the TEACHER, it's VIEWED (by the teacher)
        if ($latestVersion->evaluator_type === 'teacher') {
            return 'viewed';
        }

        return null;
    }
}
