<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notebook;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\User;
use Tymon\JWTAuth\Exceptions\JWTException;

class NotebookController extends Controller
{
    public function store(Request $request){
        
        
        $validated = $request->validate([
            'title' => 'required|string|max:300',
            'content' => 'required|string',
            'user_id' => 'required',
        ]);

        
        $notebook = Notebook::create([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'user_id' => $validated['user_id'],
        ]);

       
        return response()->json([
            'message' => 'success',
            'notebook' => [
                'id' => $notebook->id,
                'user_id' => $notebook->user_id,
                'title' => $notebook->title,
                'content' => $notebook->content,
            ]
        ]);
    
    }
    public function show($user_id)
    {
        if (empty($user_id) || !is_numeric($user_id)) {
            return response()->json(['message' => 'user_id không hợp lệ'], 422);
        }
    
        $notebook = Notebook::where('user_id', $user_id)->get();
    
        if ($notebook->isEmpty()) {
            return response()->json(['message' => 'Notebook không tồn tại','notebook' => $notebook]);
        }
    
        return response()->json([
            'message' => 'success',
            'notebook' => $notebook
        ], 200);
    }
    

    public function update(Request $request, $id) {
        $validated = $request->validate([
            'user_id' => 'required',
            'title' => 'required|string|max:300',
            'content' => 'required|string',
        ]);
    
        $notebook = Notebook::find($id);
    

        if (!$notebook) {
            return response()->json(['message' => 'Notebook không tìm thấy'], 404);
        }
    
        $notebook->update([
            'user_id' => $validated['user_id'],
            'title' => $validated['title'],
            'content' => $validated['content'],
        ]);
    
        return response()->json([
            'message' => 'Notebook đã được cập nhật',
            'notebook' => $notebook,
        ], 200);
    }
    
    public function destroy($id, $user_id)
    {
        if (empty($user_id) || !is_numeric($user_id)) {
            return response()->json(['message' => 'user_id không hợp lệ'], 422);

        $notebook = Notebook::find($id);

        if (!$notebook) {
            return response()->json(['message' => 'notebook không tìm thấy'], 404);
        }

        // Xóa notebook
        $notebook->delete();

        return response()->json([
            'message' => 'Notebook đã xóa',
            'data' => $notebook,
        ]);
    }
}
}