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
        // Xác thực dữ liệu đầu vào
        $request->validate([
            'user_id' => 'required', // Bắt buộc phải có user_id
            'content' => 'required|string|max:255', // Nội dung bài viết bắt buộc, tối đa 255 ký tự
            'thumbnails' => 'required|array', // Thumbnails là bắt buộc và có thể là mảng
            'thumbnails.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048', // Mỗi phần tử trong thumbnails phải là ảnh, tối đa 2MB
        ]);

        // Tạo một đối tượng Post mới
        $post = new Post();
        $post->user_id = $request->input('user_id');
        $post->content = $request->input('content');
        $post->save(); // Lưu bài viết trước khi thêm hình ảnh vào gallery_post

        // Khởi tạo mảng để lưu tên các ảnh
        $imagePaths = [];

        // Xử lý nếu có nhiều ảnh được tải lên
        if ($request->hasFile('thumbnails')) {
            foreach ($request->file('thumbnails') as $file) {
                // Tạo tên file duy nhất cho mỗi ảnh
                $fileName = time() . '_' . $file->getClientOriginalName();

                // Lưu ảnh vào thư mục 'uploads' trong storage
                $path = $file->storeAs('uploads', $fileName, 'public');

                // Lưu thông tin vào bảng gallery_post
                $gallery = new Gallery_post();
                $gallery->post_id = $post->id; // Liên kết ảnh với bài viết
                $gallery->thumbnail = $fileName; // Lưu tên file
                $gallery->save();

                // Thêm tên ảnh vào mảng
                $imagePaths[] = $fileName;
            }
        }

        // Lưu tên ảnh vào bảng posts (nếu bạn muốn)
        $post->thumbnail = json_encode($imagePaths); // Nếu bạn muốn lưu tên ảnh vào bảng posts
        $post->save(); // Lưu lại bài viết nếu đã thêm trường images

        // Trả về phản hồi JSON
        return response()->json([
            'data' => $post,
            'message' => 'Thêm bài viết và hình ảnh thành công'
        ], 201);
    }


    public function show(string $id)
    {

        $post = Post::join('users', 'post.user_id', '=', 'users.id')
            ->where('post.user_id', $id)
            ->select('post.id', 'post.content', 'post.thumbnail', 'post.created_at', 'users.name as user_name', 'users.avatar as user_avatar')
            ->get();


        // Chuyển đổi dữ liệu để tách user thông tin
        $formattedPosts = $post->map(function ($post) {
            $likepostCount = Like_post::where('post_id', $post->id)->count();
            $commentpostCount = Comment_post::where('post_id', $post->id)->count();
            return [
                'id' => $post->id,
                'content' => $post->content,
                'thumbnail' => $post->thumbnail,
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
            'message' => 'xoa thanh cong r babie',
        ], 200);
    }
}
