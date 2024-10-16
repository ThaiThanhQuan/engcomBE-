<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonExercise extends Model
{
    use HasFactory;
    protected $table = 'lesson_exercise';
    protected $fillable = ['id', 'lesson_id', 'title','text'];

    public $timestamps = false;

    public function exerciseOptions()
    {
        return $this->hasMany(ExerciseOptions::class);
    }
}
