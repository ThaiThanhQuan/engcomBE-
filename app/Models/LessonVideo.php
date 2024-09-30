<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonVideo extends Model
{
    use HasFactory;
    protected $table = 'lesson_video';
    protected $fillable = ['id', 'content', 'video','lesson_id'];

    public $timestamps = false;
}
