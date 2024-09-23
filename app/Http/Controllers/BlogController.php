<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Blog;
use Validator;
class BlogController extends Controller
{
    // Đọc tất cả Blogs
    public function index()
    {
        $blogs = Blog::all();
        return response()->json($blogs);
    }
    // Tạo Blog
    public function store(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);
        if ($validator->fails()) {
            $arr = [
                'success' => false,
                'message' => 'Thêm thất bại! ',
                'data' => $validator->errors(),
            ];
            return response()->json($arr);
        }
        $blog = Blog::create($input);
        $arr = [
            'status' => true,
            'message' => "Thêm thành công",
            'data' => $blog
        ];

        return response()->json($arr, 201);
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
            return response()->json($arr, 404);
        }
        $arr = [
            'success' => true,
            'message' => 'Chi tiết bài viết',
            'data' => $blog
        ];
        return response()->json($arr, 201);
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
        ];
        return response()->json($arr, 200);
    }
}
