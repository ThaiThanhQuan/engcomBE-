<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Progress extends Model
{
    use HasFactory;
    protected $table = 'progress';
    protected $fillable = [
        'id',
        'user_id',
        'course_id',
        'lesson_id',
        'is_completed',
        'is_in_progress'
    ];
    public $timestamps = false;
    
}
