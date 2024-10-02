<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscribe extends Model
{
    use HasFactory;
    protected $table = 'subsribe';
    protected $fillable = [
        'id',
        'class_id',
        'user_id',
    ];
    public $timestamps = true;
}
