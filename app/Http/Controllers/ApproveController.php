<?php

namespace App\Http\Controllers;

use App\Models\Classes;
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

    public function update(Request $request, string $class_id)
    {
        $class = Classes::find($class_id);
        if (!$class) {
            return response()->json(['message' => 'Class not found'], 404);
        }
        $class->deleted = $request->input('deleted');
        $class->save();
        return response()->json(['message' => 'Class updated successfully', 'class' => $class], 200);
    }
}
