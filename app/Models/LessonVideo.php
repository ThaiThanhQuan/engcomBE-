<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonVideo extends Model
{
    use HasFactory;
    protected $table = 'lesson_video';
    protected $fillable = ['id', 'text', 'title','lesson_id'];

    public $timestamps = false;
}
