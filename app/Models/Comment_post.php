<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment_post extends Model
{
    use HasFactory;

    protected $table ='comment_post';

    protected $fillable = ['user_id', 'post_id', 'content'];

    public $timestamps = 'true';

    const UPDATED_AT = null;

    // Hoặc bạn có thể ghi đè phương thức setUpdatedAt để tránh cập nhật updated_at
    public function setUpdatedAt($value)
    {
        // Không làm gì cả, để ngăn việc cập nhật cột updated_at
    }
}
