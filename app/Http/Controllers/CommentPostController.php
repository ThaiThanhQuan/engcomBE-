<?php

namespace App\Http\Controllers;

use App\Models\Comment_post;
use Illuminate\Http\Request;

class CommentPostController extends Controller
{
    
    public function index()
    {
        //
    }

  

    public function store(Request $request)
    {
        $commentpost = new Comment_post();
        $commentpost->user_id = $request->input('user_id');
        $commentpost->post_id = $request->input('post_id');
        $commentpost->content = $request->input('content');
        $commentpost->save();

        return response()->json([
            'data' => $commentpost,
            'message' => 'thanh cong'
        ], 201);
    }

    public function show(string $id)
    {
        
    }

    public function destroy(string $id)
    {
        $commentpost = Comment_post::find($id);

        if (!$commentpost) {
            return response()->json([
                'message' => 'commentpost not found'
            ], 404);
        }

        
        $commentpost->delete();

    
        return response()->json([
            'message' => 'xoa thanh cong ',
        ], 200); 
    }
}
