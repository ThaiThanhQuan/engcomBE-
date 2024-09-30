<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    use HasFactory;

    
    protected $table = 'lesson';
    protected $fillable = ['id', 'type', 'name','course_id'];

    public $timestamps = false;
    public function videos()
    {
        return $this->hasMany(LessonVideo::class);
    }

    public function lessonText()
    {
        return $this->hasMany(LessonText::class);
    }

    public function lessonExercises()
    {
        return $this->hasMany(LessonExercise::class);
    }
}
