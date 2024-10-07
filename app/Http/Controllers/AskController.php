<?php

namespace App\Http\Controllers;

use App\Models\Ask;
use App\Models\User;
use Illuminate\Http\Request;

class AskController extends Controller
{
    public function index()
    {
        //
    }
    public function store(Request $request)
    {
        // Tạo một Ask mới
        $ask = Ask::create([
            'lesson_id' => $request->lesson_id,
            'user_id' => $request->user_id,
            'parent_id' => $request->parent_id,
            'content' => $request->content,
        ]);

        // Lấy thông tin người dùng
        $user = User::find($ask->user_id);

        // Trả về phản hồi thành công
        return response()->json([
            'message' => 'Ask created successfully',
            'data' => [
                'ask' => $ask,
                'user' => $user ? [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'avatar' => $user->avatar,
                ] : null,
            ],
        ], 201);
    }

    public function show(string $lesson_id)
    {
        // Tìm các Ask dựa trên lesson_id và parent_id là null
        $asks = Ask::where('lesson_id', $lesson_id)
                    ->whereNull('parent_id')
                    ->get();
    
        if ($asks->isEmpty()) {
            return response()->json(['message' => 'No asks found for this lesson'], 404);
        }
    
        // Lấy thông tin người dùng từ các ask
        $asksWithUserInfo = $asks->map(function ($ask) {
            // Lấy thông tin người dùng dựa trên user_id trong ask
            $user = User::find($ask->user_id);
    
            return [
                'ask' => $ask,
                'user' => $user ? [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'avatar' => $user->avatar,
                ] : null,
            ];
        });
    
        return response()->json([
            'data' => $asksWithUserInfo,
        ]);
    }
    public function update(Request $request, string $id)
    {
        //
    }
    public function destroy(string $id)
    {
        // Tìm Ask theo ID
        $ask = Ask::find($id);
        
        // Kiểm tra xem Ask có tồn tại không
        if (!$ask) {
            return response()->json(['message' => 'Ask not found'], 404);
        }
    
        // Lưu trữ dữ liệu để trả về sau khi xóa
        $deletedAsk = $ask->fresh(); // Lấy dữ liệu hiện tại của Ask trước khi xóa
        
        // Xóa Ask
        $ask->delete();
    
        // Lấy thông tin người dùng liên quan
        $user = User::find($deletedAsk->user_id);
        
        // Trả về phản hồi với dữ liệu đã xóa
        return response()->json([
            'message' => 'Ask deleted successfully',
            'data' => [
                'ask' => $deletedAsk,
                'user' => $user ? [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'avatar' => $user->avatar,
                ] : null,
            ],
        ], 200);
    }
    
    public function reply(string $lesson_id)
    {
        $asks = Ask::where('lesson_id', $lesson_id)
            ->whereNotNull('parent_id')
            ->get();
    
        if ($asks->isEmpty()) {
            return response()->json(['message' => 'No asks found for this lesson'], 404);
        }
    
        // Lấy thông tin người dùng từ các ask
        $asksWithUserInfo = $asks->map(function ($ask) {
            // Lấy thông tin người dùng dựa trên user_id trong ask
            $user = User::find($ask->user_id);
    
            return [
                'ask' => $ask,
                'user' => $user ? [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'avatar' => $user->avatar,
                ] : null,
            ];
        });
    
        return response()->json([
            'data' => $asksWithUserInfo,
        ]);
    }

}
