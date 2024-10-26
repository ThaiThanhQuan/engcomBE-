<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notebook;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\User;
use Tymon\JWTAuth\Exceptions\JWTException;

class NotebookController extends Controller
{
    public function store(Request $request)
    {
        $notebook = Notebook::create([
            'title' => $request->title,
            'content' => $request->content,
            'user_id' => $request->user_id,
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
            return response()->json(['message' => 'Notebook không tồn tại', 'notebook' => $notebook]);
        }
        return response()->json([
            'message' => 'success',
            'notebook' => $notebook
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $notebook = Notebook::find($id);
        if (!$notebook) {
            return response()->json(['message' => 'Notebook không tìm thấy'], 404);
        }
        $notebook->update([
            'user_id' => $request->user_id,
            'title' => $request->title,
            'content' => $request->content,
        ]);
        return response()->json([
            'message' => 'Notebook đã được cập nhật',
            'notebook' => $notebook,
        ], 200);
    }

    public function destroy($id)
    {
        $notebook = Notebook::find($id);
        if (!$notebook) {
            return response()->json(['message' => 'notebook không tìm thấy'], 404);
        }
        $notebook->delete();
        return response()->json([
            'message' => 'Notebook đã xóa',
            'data' => $notebook,
        ]);
    }
}