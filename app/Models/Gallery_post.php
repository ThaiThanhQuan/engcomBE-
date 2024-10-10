<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gallery_post extends Model
{
    use HasFactory;

    protected $table ='gallery_post';

    protected $fillable = ['post_id', 'thumbnail'];

    public $timestamps = 'true';
    const UPDATED_AT = null;

    // Hoặc bạn có thể ghi đè phương thức setUpdatedAt để tránh cập nhật updated_at
    public function setUpdatedAt($value)
    {
        // Không làm gì cả, để ngăn việc cập nhật cột updated_at
    }
    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
