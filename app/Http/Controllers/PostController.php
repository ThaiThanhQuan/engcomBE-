<?php

namespace App\Http\Controllers;

use App\Models\Comment_post;
use App\Models\Gallery_post;
use App\Models\Like_post;
use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index()
    {
        //
    }
    public function show($postId)
    {
        // Lấy tất cả bài đăng mà user_id không phải là $userId
        $post = Post::join('users', 'post.user_id', '=', 'users.id')
            ->leftJoin('gallery_post', 'post.id', '=', 'gallery_post.post_id')
            ->where('post.id', $postId)
            ->select(
                'users.id as user_id',
                'users.name as user_name',
                'users.avatar as user_avatar',
                'post.id as post_id',
                'post.content as post_content',
                'post.thumbnail as post_thumbnail',
                'post.created_at as created_at' // Thêm trường created_at từ post
            )
            ->first();

        // Kiểm tra xem có bài đăng nào không
        if (!$post) {
            return response()->json([
                'message' => 'No posts found'
            ], 404);
        }

        // Đếm số lượng likes và comments
        $likepostCount = Like_post::where('post_id', $postId)->count();
        $commentpostCount = Comment_post::where('post_id', $postId)->count();

        // Lấy tất cả thumbnails từ bảng gallery_post
        $galleryThumbnails = Gallery_post::where('post_id', $postId)
            ->pluck('thumbnail')
            ->toArray(); // Chuyển đổi thành mảng

        // Chọn các trường cần thiết và loại bỏ user_id
        return response()->json([
            'id' => $post->post_id,
            'content' => $post->post_content,
            'thumbnails' => $galleryThumbnails,
            'created_at' => $post->created_at,
            'likecount' => $likepostCount,
            'commentcount' => $commentpostCount,
            'user' => [
                'user_id' => $post->user_id,
                'name' => $post->user_name,
                'avatar' => $post->user_avatar,
            ],
        ]);
    }

    public function getAll($userId)
    {
        // Lấy tất cả bài đăng mà user_id không phải là $userId
        $posts = Post::where('user_id', '!=', $userId)->get();

        // Kiểm tra xem có bài đăng nào không
        if ($posts->isEmpty()) {
            return response()->json([
                'message' => 'No posts found'
            ], 404);
        }

        // Khởi tạo mảng để lưu các bài đăng
        $postsData = $posts->map(function ($post) {
            // Đếm số lượng likes và comments
            $likeCount = Like_post::where('post_id', $post->id)->count();
            $commentCount = Comment_post::where('post_id', $post->id)->count();

            // Lấy tất cả thumbnails từ bảng gallery_post
            $galleryThumbnails = Gallery_post::where('post_id', $post->id)
                ->pluck('thumbnail')
                ->toArray();

            // Lấy thông tin người dùng
            $user = $post->user;
            return [
                'id' => $post->id,
                'content' => $post->content,
                'thumbnails' => $galleryThumbnails,
                'created_at' => $post->created_at,
                'likecount' => $likeCount,
                'commentcount' => $commentCount,
                'user' => [
                    'user_id' => $post->user_id,
                    'name' => $user->name,
                    'avatar' => $user->avatar,
                ],
            ];
        });

        // Trả về danh sách bài đăng
        return response()->json([
            'data' => $postsData
        ], 200);
    }

    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
