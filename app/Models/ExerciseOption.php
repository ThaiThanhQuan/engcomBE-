<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExerciseOption extends Model
{
    use HasFactory;
    protected $table = 'exercise_options';
    protected $fillable = ['id', 'lesson_exercise_id', 'text','is_correct'];

    public $timestamps = false;

}
