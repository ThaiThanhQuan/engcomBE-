<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;

class GetBlogsController extends Controller
{
    public function index()
    {
        $blogs = DB::table('blogs')
            ->where('blogs.deleted', 1)
            ->join('users', 'blogs.user_id', '=', 'users.id')
            ->select('blogs.id','users.name', 'blogs.title', 'blogs.thumbnail', 'blogs.content', 'blogs.created_at', 'blogs.updated_at')
            ->get();
        return response()->json([
            'data' => $blogs
        ]);

    }
    public function store(Request $request)
    {
        //
    }
    public function show(string $id)
    {

    }
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}