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

    public function index()
    {

    }
    public function store(Request $request)
    {
        // Validate incoming request data
        $request->validate([
            'user_id' => 'required',
            'content' => 'required|string|max:255',
        ]);

        // Create a new post
        $post = new Post();
        $post->user_id = $request->input('user_id');
        $post->content = $request->input('content');
        $post->save();

        // Array to hold image paths
        $imagePaths = [];

        // Process uploaded images
        if ($request->hasFile('thumbnails')) {
            foreach ($request->file('thumbnails') as $file) {
                // Create a unique name for each image
                $fileName = time() . '_' . uniqid() . '_' . $file->getClientOriginalName();

                // Store the image in the 'uploads' directory
                $path = $file->storeAs('uploads', $fileName, 'public');

                // Save image information to the gallery_post table
                $gallery = new Gallery_post();
                $gallery->post_id = $post->id;
                $gallery->thumbnail = $fileName;
                $gallery->save();

                // Add the image name to the array
                $imagePaths[] = $fileName;
            }
        }

        // Optionally save image names to the posts table
        $post->thumbnail = json_encode($imagePaths);
        $post->save();

        // Return a JSON response
        return response()->json([
            'data' => [
                'user_id' => $post->user_id,
                'content' => $post->content,
                'created_at' => $post->created_at,
                'id' => $post->id,
                'thumbnails' => $imagePaths, // Chuyển đổi thumbnail thành mảng
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

            
        // Chuyển đổi dữ liệu để tách user thông tin
        $formattedPosts = $post->map(function ($post) {
            $likepostCount = Like_post::where('post_id', $post->id)->count();
            $commentpostCount = Comment_post::where('post_id', $post->id)->count();
            $galleryThumbnails = Gallery_post::where('post_id', $post->id)
            ->pluck('thumbnail')
            ->toArray(); // Chuyển đổi thành mảng
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
        // Xóa thumbnail nếu có
        // Xóa thumbnail nếu có
        // Xóa thumbnail nếu có
        if ($post->thumbnail) {
            $thumbnailPath = storage_path('app/public/uploads/' . $post->thumbnail); // Đường dẫn đến thumbnail
            if (file_exists($thumbnailPath)) {
                unlink($thumbnailPath); // Xóa file thumbnail
            }
        }

        // Xóa tất cả ảnh trong gallery_post
        $galleryImages = Gallery_post::where('post_id', $post->id)->get();
        foreach ($galleryImages as $galleryImage) {
            $imagePath = storage_path('app/public/uploads/' . $galleryImage->thumbnail); // Đường dẫn đến ảnh trong gallery
            if (file_exists($imagePath)) {
                unlink($imagePath); // Xóa file ảnh trong gallery
            }
            // Xóa record trong gallery_post
            $galleryImage->delete();
        }

        // Cuối cùng, xóa bài viết
        $post->delete();
        return response()->json([
            'data' => $post,
            'message' => 'xoa thanh cong ',
        ], 200);
    }
}
