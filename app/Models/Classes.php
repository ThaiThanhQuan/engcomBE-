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
    public function subscribes()
    {
        return $this->hasMany(Subscribe::class, 'class_id');
        // Giả sử một lớp có nhiều lượt đăng ký
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id'); // 'user_id' là khóa ngoại trong bảng classes
    }
    public function comments()
    {
        return $this->hasMany(Comment::class, 'class_id'); // class_id là khóa ngoại trong bảng comments
    }
}
