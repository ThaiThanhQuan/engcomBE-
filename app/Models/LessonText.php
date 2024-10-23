<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonText extends Model
{
    use HasFactory;
    protected $table = 'lesson_text';
    protected $fillable = ['id', 'text','title', 'lesson_id'];

    public $timestamps = false;
}
