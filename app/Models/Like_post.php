<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Like_post extends Model
{
    use HasFactory;

    protected $table ='like_post';

    protected $fillable = ['post_id', 'user_id', 'parent_id'];

    public $timestamps = false;
    
}
