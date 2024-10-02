<?php

namespace App\Http\Controllers;

use App\Models\Classes;
use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Tạo comment mới
        $comment = new Comment();
        $comment->class_id = $request->input('class_id'); 
        $comment->user_id = $request->input('user_id');  
        $comment->parent_id = $request->input('parent_id'); 
        $comment->content = $request->input('content');   
        $comment->save(); 
    
        // Trả về phản hồi
        return response()->json([
            'data' => $comment,
            'message' => 'Comment created successfully'
        ], 201); 
    }
    


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $comments = Comment::join('users', 'comments.user_id', '=', 'users.id')
            ->where('comments.class_id', $id)
            ->whereNull('comments.parent_id') 
            ->select('comments.*', 'users.id as user_id', 'users.name as user_name', 'users.avatar as user_avatar')
            ->get();
        
        // Chuyển đổi dữ liệu để tách user thông tin
        $formattedComments = $comments->map(function ($comment) {
            return [
                'id' => $comment->id,
                'class_id' => $comment->class_id,
                'content' => $comment->content,
                'created_at' => $comment->created_at,
                'updated_at' => $comment->updated_at,
                'user' => [
                    'id' => $comment->user_id,
                    'name' => $comment->user_name,
                    'avatar' => $comment->user_avatar,
                ],
            ];
        });

        // Đưa ra kết quả
        return response()->json([
            'data' => $formattedComments,
            'message' => 'success'
        ]);
    }

    
    
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $comment = Comment::find($id);

        if (!$comment) {
            return response()->json([
                'message' => 'Comment not found'
            ], 404);
        }

        // Xóa comment
        $comment->delete();

        // Trả về phản hồi thành công
        return response()->json([
            'message' => 'Comment deleted successfully',
            'data' => $comment
        ], 200); // Mã trạng thái 200 cho thành công
    }

    public function showResponse(string $id)
    {
        // Lấy tất cả comment có parent_id không null cho class_id cụ thể
        $comments = Comment::join('users', 'comments.user_id', '=', 'users.id')
            ->where('comments.class_id', $id)
            ->whereNotNull('comments.parent_id')
            ->select('comments.*', 'users.id as user_id', 'users.name as user_name', 'users.avatar as user_avatar')
            ->get();
    
        // Chuyển đổi dữ liệu để đưa user thông tin vào
        $formattedComments = $comments->map(function ($comment) {
            return [
                'id' => $comment->id,
                'class_id' => $comment->class_id,
                'content' => $comment->content,
                'parent_id' => $comment->parent_id,
                'created_at' => $comment->created_at,
                'updated_at' => $comment->updated_at,
                'user' => [
                    'id' => $comment->user_id,
                    'name' => $comment->user_name,
                    'avatar' => $comment->user_avatar,
                ],
            ];
        });
    
        // Đưa ra kết quả
        return response()->json([
            'data' => $formattedComments,
            'message' => 'success'
        ]);
    }
    
    
}
