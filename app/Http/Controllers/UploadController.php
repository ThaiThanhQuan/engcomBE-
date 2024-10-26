<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    public function cart(Request $request)
    {
        $request->validate([
            'file' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('uploads', $fileName, 'public');
            return response()->json([
                'success' => true,
                'url' => $fileName,
            ]);
        }
        return response()->json([
            'success' => false,
            'message' => 'No file uploaded.',
        ], 400);
    }

    public function deleteCart(Request $request)
    {
        $request->validate([
            'url' => 'required|string',
        ]);
        $fileName = $request->input('url');
        $fullPath = 'uploads/' . $fileName;
        if (Storage::disk('public')->exists($fullPath)) {
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
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('uploads/videos', $fileName, 'public');
            return response()->json([
                'success' => true,
                'url' => $fileName,
            ]);
        }
        return response()->json([
            'success' => false,
            'message' => $request,
        ], 400);
    }

    public function deleteVideo(Request $request)
    {
        $request->validate([
            'url' => 'required|string',
        ]);
        $fileName = $request->input('url');
        $fullPath = 'uploads/videos/' . $fileName;
        if (Storage::disk('public')->exists($fullPath)) {
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
