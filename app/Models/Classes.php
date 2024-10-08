<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classes extends Model
{
    use HasFactory;
    
    protected $table = 'classes';

    protected $fillable = ['name', 'user_id', 'description', 'thumbnail', 'deleted','password','type','subject'];

    public $timestamps = true;
}
