<?php

namespace App\Http\Controllers;

use App\Models\saveBlog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SaveBlogController extends Controller
{
    public function store(string $user_id, int $blog_id)
    {
        $userBlog = saveBlog::create([
            'user_id' => $user_id,
            'blog_id' => $blog_id,
        ]);
        return response()->json([
            'success' => true,
            'message' => 'Thêm thành công',
            'data' => $userBlog,
        ], 201);
    }

    public function show(string $user_id)
    {
        $posts = DB::table('save-blogs')
            ->join('blogs', 'save-blogs.blog_id', '=', 'blogs.id')
            ->select('blogs.id as blog_id', 'save-blogs.user_id', 'blogs.title', 'blogs.content', 'save-blogs.id', 'blogs.updated_at')
            ->where('save-blogs.user_id', $user_id)
            ->get();
        return response()->json([
            'success' => true,
            'data' => $posts,
        ]);
    }
    public function destroy(string $user_id, int $blog_id)
    {
        $userBlog = saveBlog::where('user_id', $user_id)
            ->where('blog_id', $blog_id)
            ->first();
        if (!$userBlog) {
            return response()->json([
                'success' => false,
                'message' => 'Bài viết không tồn tại hoặc không thuộc về người dùng này.',
            ], 404);
        }
        $userBlog->delete();
        return response()->json([
            'success' => true,
            'message' => 'Bài viết đã được xóa thành công.',
            'data' => $userBlog
        ]);
    }
}
