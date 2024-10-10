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
        // $request->validate([
        //     'user_id' => 'required',
        //     'content' => 'required|string|max:255',
            
        //     'video' => 'nullable|string|max:255',
        //     'thumbnail' => 'required|array', 
        //     'thumbnails.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048', 
        // ]);
        var_dump($request);

        $post = new Post();
        $post->user_id = $request->input('user_id');
        $post->content = $request->input('content');
        
        $post->thumbnail = $request->input('thumbnail');
        $post->save();

        if ($request->hasFile('thumbnails')) {
            foreach ($request->file('thumbnails') as $file) {
                $fileName = time() . '_' . $file->getClientOriginalName();
                // Lưu file vào storage
                $file->storeAs('uploads', $fileName, 'public');
    
                // Lưu vào bảng gallery
                $gallery = new Gallery_post();
                $gallery->post_id = $post->id; // Liên kết ảnh với bài viết
                $gallery->image_path = $fileName; // Lưu tên file
                $gallery->save();
            }
        }

        return response()->json([
            'data' => $post,
            'message' => 'thanh cong mien man~'
        ], 201);
    }


    public function show(string $id)
    {

        $post = Post::join('users', 'post.user_id', '=', 'users.id')
            ->where('post.user_id', $id)
            ->select('post.id', 'post.content', 'post.thumbnail', 'post.video', 'post.created_at', 'users.name as user_name', 'users.avatar as user_avatar')
            ->get();


        // Chuyển đổi dữ liệu để tách user thông tin
        $formattedPosts = $post->map(function ($post) {
            $likepostCount = Like_post::where('post_id', $post->id)->count();
            $commentpostCount = Comment_post::where('post_id', $post->id)->count();
            return [
                'id' => $post->id,
                'content' => $post->content,
                'thumbnail' => $post->thumbnail,
                'video' => $post->video,
                'created_at' => $post->created_at,
                'likecount' => $likepostCount,
                'commentcount' => $commentpostCount,
                'user' => [
                    'name' => $post->user_name,
                    'avatar' => $post->user_avatar,
                ],
            ];
        });
        return response()->json([
            'data' => $formattedPosts,
            'message' => 'thanh cong mien man~'
        ]);
    }

    public function destroy(string $id)
    {
        $post = Post::find($id);

        if (!$post) {
            return response()->json([
                'message' => 'post not found'
            ], 404);
        }

        $post->comment_post()->delete();
        $post->like_post()->delete();
        $post->delete();

    
        return response()->json([
            'data' => $post,
            'message' => 'xoa thanh cong r babie',
        ], 200); 
    }
}
