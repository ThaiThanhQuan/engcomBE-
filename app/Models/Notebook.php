<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notebook extends Model
{
    use HasFactory;
    protected $table = 'notebook';
    protected $fillable = [
        'title',
        'content',
        'user_id'
    ];

}
