<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    public function cart(Request $request)
    {
        // Xác thực file upload
        $request->validate([
            'file' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Tối đa 2MB
        ]);

        // Kiểm tra xem có file upload không
        if ($request->hasFile('file')) {
            // Lấy file
            $file = $request->file('file');

            // Tạo tên file duy nhất
            $fileName = time() . '_' . $file->getClientOriginalName();

            // Lưu file vào storage
            $path = $file->storeAs('uploads', $fileName, 'public');

            // Trả về phản hồi với chỉ tên file
            return response()->json([
                'success' => true,
                'url' => $fileName, // Chỉ trả về tên file
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No file uploaded.',
        ], 400);
    }

    public function deleteCart(Request $request)
    {
        // Xác thực input
        $request->validate([
            'url' => 'required|string', // Đảm bảo rằng 'url' được gửi lên
        ]);
    
        $fileName = $request->input('url'); // Nhận tên file
        $fullPath = 'uploads/' . $fileName; // Tạo đường dẫn
    
        // Kiểm tra xem file có tồn tại không
        if (Storage::disk('public')->exists($fullPath)) {
            // Xóa file
            Storage::disk('public')->delete($fullPath);
    
            return response()->json([
                'success' => true,
                'message' => 'File deleted successfully.',
            ]);
        }
    
        return response()->json([
            'success' => false,
            'message' => 'File not found.',
        ], 404);
    }
 
    public function uploadVideo(Request $request)
    {
        // Xác thực file upload
        // Kiểm tra xem có file upload không
        if ($request->hasFile('file')) {
            // Lấy file
            $file = $request->file('file');

            // Tạo tên file duy nhất
            $fileName = time() . '_' . $file->getClientOriginalName();

            // Lưu file vào storage
            $path = $file->storeAs('uploads/videos', $fileName, 'public');

            // Trả về phản hồi với chỉ tên file
            return response()->json([
                'success' => true,
                'url' => $fileName, // Trả về tên file
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $request,
        ], 400);
    }

    public function deleteVideo(Request $request)
    {
        // Xác thực input
        $request->validate([
            'url' => 'required|string', 
        ]);
    
        $fileName = $request->input('url'); // Nhận tên file
        $fullPath = 'uploads/videos/' . $fileName; // Tạo đường dẫn cho video
    
        // Kiểm tra xem file có tồn tại không
        if (Storage::disk('public')->exists($fullPath)) {
            // Xóa file
            Storage::disk('public')->delete($fullPath);
    
            return response()->json([
                'success' => true,
                'message' => 'Video deleted successfully.',
            ]);
        }
    
        return response()->json([
            'success' => false,
            'message' => 'Video not found.',
        ], 404);
    }
    
}
