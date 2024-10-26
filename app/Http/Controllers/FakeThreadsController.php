<?php

namespace App\Http\Controllers;

use App\Models\Comment_post;
use App\Models\Gallery_post;
use App\Models\Like_post;
use App\Models\Post;
use Illuminate\Http\Request;
use Validator;
use Illuminate\Support\Facades\Log;

class FakeThreadsController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'content' => 'required|string|max:255',
        ]);
        $post = new Post();
        $post->user_id = $request->input('user_id');
        $post->content = $request->input('content');
        $post->save();
        $imagePaths = [];
        if ($request->hasFile('thumbnails')) {
            foreach ($request->file('thumbnails') as $file) {
                $fileName = time() . '_' . uniqid() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('uploads', $fileName, 'public');
                $gallery = new Gallery_post();
                $gallery->post_id = $post->id;
                $gallery->thumbnail = $fileName;
                $gallery->save();
                $imagePaths[] = $fileName;
            }
        }
        $post->thumbnail = json_encode($imagePaths);
        $post->save();
        return response()->json([
            'data' => [
                'user_id' => $post->user_id,
                'content' => $post->content,
                'created_at' => $post->created_at,
                'id' => $post->id,
                'thumbnails' => $imagePaths,
            ],
            'message' => 'Thêm bài viết và hình ảnh thành công'
        ], 201);
    }

    public function show(string $id)
    {
        $post = Post::join('users', 'post.user_id', '=', 'users.id')
            ->where('post.user_id', $id)
            ->select('post.id', 'post.content', 'post.thumbnail', 'post.created_at', 'post.user_id', 'users.name as user_name', 'users.avatar as user_avatar')
            ->get();
        $formattedPosts = $post->map(function ($post) {
            $likepostCount = Like_post::where('post_id', $post->id)->count();
            $commentpostCount = Comment_post::where('post_id', $post->id)->count();
            $galleryThumbnails = Gallery_post::where('post_id', $post->id)
                ->pluck('thumbnail')
                ->toArray();
            return [
                'id' => $post->id,
                'content' => $post->content,
                'thumbnails' => $galleryThumbnails,
                'created_at' => $post->created_at,
                'likecount' => $likepostCount,
                'commentcount' => $commentpostCount,
                'user' => [
                    'user_id' => $post->user_id,
                    'name' => $post->user_name,
                    'avatar' => $post->user_avatar,
                ],
            ];
        });
        return response()->json([
            'data' => $formattedPosts,
            'message' => 'thanh cong '
        ]);
    }

    public function destroy(string $id)
    {
        $post = Post::find($id);
        if (!$post) {
            return response()->json([
                'message' => 'Post not found'
            ], 404);
        }
        $post->comment_post()->delete();
        $post->like_post()->delete();
        if ($post->thumbnail) {
            $thumbnailPath = storage_path('app/public/uploads/' . $post->thumbnail);
            if (file_exists($thumbnailPath)) {
                unlink($thumbnailPath);
            }
        }
        $galleryImages = Gallery_post::where('post_id', $post->id)->get();
        foreach ($galleryImages as $galleryImage) {
            $imagePath = storage_path('app/public/uploads/' . $galleryImage->thumbnail);
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
            $galleryImage->delete();
        }
        $post->delete();
        return response()->json([
            'data' => $post,
            'message' => 'xoa thanh cong ',
        ], 200);
    }
}
