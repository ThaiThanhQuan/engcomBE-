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

    public function show(Request $request){
        $userId = $request->query('user_id');
        $notebookId = $request->query('id');
    
        if (!$userId || !$notebookId) {
            return response()->json(['message' => 'thieu user_id hoac id'], 400);
        }

        $notebook = Notebook::where('user_id', $userId)
                            ->where('id', $notebookId)
                            ->first();
        if (!$notebook) {
            return response()->json(['message' => 'Notebook khong ton tai'], 404);
        }
    
        return response()->json([
            'message' => 'success',
            'notebook' => $notebook
        ], 200);
    }
    

    public function update(Request $request){
        $validated = $request->validate([
            'id' => 'required',
            'user_id' => 'required',
            'title' => 'required|string|max:300',
            'content' => 'required|string',
        ]);

        $notebook = Notebook::where('id', $validated['id'])->where('user_id', $validated['user_id'])->first();

        if (!$notebook) {
            return response()->json(['message' => 'notebook khong tim thay'], 404);
        }

        $notebook->update([
            'title' => $validated['title'],
            'content' => $validated['content'],
        ]);

       
        return response()->json([
        'message' => 'Notebook da duoc update',
        'notebook' => [
            'id' => $notebook->id,
            'user_id' => $notebook->user_id,
            'title' => $notebook->title,
            'content' => $notebook->content,
        ]
    ]);
    }

    public function destroy(Request $request){
        $validated = $request->validate([
            'id' => 'required',
            'user_id' => 'required',
        ]);

        $notebook = Notebook::where('id', $validated['id'])->where('user_id', $validated['user_id'])->first();

        if (!$notebook) {
            return response()->json(['message' => 'notebook khong tim thay'], 404);
        }

        $notebook->delete();
          
  

       
        return response()->json([
        'message' => 'Notebook da xoa',
    ]);
    }
}
