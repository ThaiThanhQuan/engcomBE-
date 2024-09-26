<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Blog;
use Illuminate\Support\Facades\DB;
use Validator;
class BlogController extends Controller
{
    // Đọc tất cả Blogs
  public function index()
{
    $blogs = DB::table('blogs')
        ->join('users', 'blogs.user_id', '=', 'users.id')
        ->select('blogs.id', 'blogs.title', 'blogs.user_id', 'blogs.content', 'users.name', 'users.avatar')
        ->get();

    $formattedBlogs = $blogs->map(function ($blog) {
        return [ 
            'blog' => [
                'id' => $blog->id,
                'user_id' => $blog->user_id,
                'title' => $blog->title,
                'content' => $blog->content,
            ],
            'user' => [
                'user' => $blog->name,
                'img' => $blog->avatar,
            ],
        ];
    });

    return response()->json(['data' => $formattedBlogs,'message' => 'success', 'status' =>true]);
}
    // Tạo Blog
    public function store(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'user_id' => 'required|integer',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Thêm thất bại!',
                'data' => $validator->errors(),
            ], 400);
        }
    
        // Tạo bài viết mới
        $blog = Blog::create($input);
    
        return response()->json([
            'status' => true,
            'message' => "Thêm thành công",
            'data' => $blog,
        ], 201);
    }    
    // Đọc Blog theo ID
    public function show($id)
    {
        $blog = Blog::find($id);
        if (!$blog) {
            $arr = [
                'success' => false,
                'message' => 'Không tìm thấy bài viết',
            ];
            return response()->json($arr, 201);
        }
        $arr = [
            'success' => true,
            'message' => 'Chi tiết bài viết',
            'data' => $blog
        ];
        return response()->json($arr, 201);
    }
    public function showList($userId)
    {
        $blogs = Blog::where('user_id', $userId)->get();
    
        if ($blogs->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy bài viết nào',
            ], 404);
        }
    
        return response()->json([
            'success' => true,
            'message' => 'Danh sách bài viết',
            'data' => $blogs,
        ], 200);
    }

    // Cập nhật Blog
    public function update(Request $request, Blog $blog)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'title' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
        ]);
        if ($validator->fails()) {
            $arr = [
                'success' => false,
                'message' => 'Cập nhập bài viết không thành công',
                'data' => $validator->errors(),
            ];
            return response()->json($arr, 200);
        }
        $blog->title = $input['title'];
        $blog->content = $input['content'];
        $blog->save();
        $arr = [
            'status' => true,
            'message' => 'Cập nhập bài viết thành công',
            'data' => $blog
        ];
        return response()->json($arr, 200);
    }

    // Xóa Blog
    public function destroy(Blog $blog)
    {
        $blog->delete();
        $arr = [
            'status' => true,
            'message' => 'Bài viết đã được xóa',
            'data' => $blog,
        ];
        return response()->json($arr, 200);
    }
    public function upload(Request $request) {
        $request->validate([
            'file' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
    
        if ($request->file('file')) {
            $path = $request->file('file')->store('images', 'public');
    
            return response()->json(['link' => asset('storage/' . $path)], 200);
        }
    
        return response()->json(['error' => 'File not uploaded'], 400);
    }
    
}
