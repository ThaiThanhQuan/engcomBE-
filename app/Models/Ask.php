<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ask extends Model
{
    use HasFactory;
    
    protected $table = 'ask';

    protected $fillable = ['class_id', 'user_id', 'lesson_id', 'content','parent_id'];

    public $timestamps = true;
}
