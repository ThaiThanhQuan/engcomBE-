<?php

namespace App\Http\Controllers;

use App\Models\Comment_post;
use App\Models\Gallery_post;
use App\Models\Like_post;
use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }
    public function show($postId)
    {
        // Lấy tất cả bài đăng mà user_id không phải là $userId
        $post = Post::join('users', 'post.user_id', '=', 'users.id')
            ->leftJoin('gallery_post', 'post.id', '=', 'gallery_post.post_id')
            // ->leftJoin('comment_post', 'post.id', '=', 'comment_post.post_id')
            ->where('post.id', $postId)
            ->select(
                'users.id as user_id',
                'users.name as user_name',
                'users.avatar as user_avatar',
                'post.id as post_id',
                'post.content as post_content',
                'post.thumbnail as post_thumbnail',
                // 'gallery_post.thumbnail as gallery_thumbnail',
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
        // Lấy tất cả các bình luận của bài đăng
        $comments = Comment_post::where('post_id', $postId)
            ->join('users', 'comment_post.user_id', '=', 'users.id')
            ->select(
                'users.id as commenter_user_id',
                'users.name as commenter_name',
                'users.avatar as commenter_avatar',
                'comment_post.content as comment_content',
                'comment_post.created_at as comment_created_at'
            )
            ->get();
        // Chọn các trường cần thiết và loại bỏ user_id
        return response()->json([
            'user_id' => $post->user_id,
            'name' => $post->user_name,
            'avatar' => $post->user_avatar,
            'post_id' => $post->post_id,
            'content' => $post->post_content,
            'thumbnails' => $galleryThumbnails,
            'likecount' => $likepostCount,
            'commentcount' => $commentpostCount,
            'comments' => $comments
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
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_avatar' => $user->avatar,
                'post_id' => $post->id,
                'content' => $post->content,
                'thumbnail' => $post->thumbnail,
                'thumbnails' => $galleryThumbnails,
                'like_count' => $likeCount,
                'comment_count' => $commentCount,
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
