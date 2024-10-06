<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class saveBlog extends Model
{
    use HasFactory;
    protected $table = 'save-blogs';
    protected $fillable = [
        'id',
        'blog_id',
        'user_id',
    ];
    public $timestamps = false;
}
