<?php

namespace App\Http\Controllers;

use App\Models\Comment_post;
use App\Models\Gallery_post;
use App\Models\Like_post;
use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function show($postId)
    {
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
                'post.created_at as created_at'
            )
            ->first();
        if (!$post) {
            return response()->json([
                'message' => 'No posts found'
            ], 404);
        }
        $likepostCount = Like_post::where('post_id', $postId)->count();
        $commentpostCount = Comment_post::where('post_id', $postId)->count();
        $galleryThumbnails = Gallery_post::where('post_id', $postId)
            ->pluck('thumbnail')
            ->toArray();
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
        $posts = Post::where('user_id', '!=', $userId)->get();
        if ($posts->isEmpty()) {
            return response()->json([
                'message' => 'No posts found'
            ], 404);
        }
        $postsData = $posts->map(function ($post) {
            $likeCount = Like_post::where('post_id', $post->id)->count();
            $commentCount = Comment_post::where('post_id', $post->id)->count();
            $galleryThumbnails = Gallery_post::where('post_id', $post->id)
                ->pluck('thumbnail')
                ->toArray();
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
        return response()->json([
            'data' => $postsData
        ], 200);
    }
}
