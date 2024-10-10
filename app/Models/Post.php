<?php

namespace App\Models;

use Carbon\Traits\Timestamp;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $table ='post';

    protected $fillable = ['user_id', 'content', 'video'];

    public $timestamps = 'true';
    const UPDATED_AT = null;

    // Hoặc bạn có thể ghi đè phương thức setUpdatedAt để tránh cập nhật updated_at
    public function setUpdatedAt($value)
    {
        // Không làm gì cả, để ngăn việc cập nhật cột updated_at
    }

    public function comment_post()
    {
        return $this->hasMany(Comment_post::class);
    }
    public function like_post()
    {
        return $this->hasMany(Like_post::class);
    }
}
