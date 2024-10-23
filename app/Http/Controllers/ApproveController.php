<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;

class ApproveController extends Controller
{
    public function index()
    {
        $approve = DB::table('classes')
            ->whereNull('classes.deleted')
            ->join('users', 'classes.user_id', '=', 'users.id')
            ->select('classes.id as classes_id', 'classes.name as classes_name', 'users.id as users_id', 'users.name as users_name', 'classes.created_at')
            ->get();
        return response()->json([
            'data' => $approve
        ]);
    }

    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    public function update(Request $request, string $id)
    {

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
