<?php

namespace App\Http\Controllers;

use App\Models\Classes;
use DB;
use Illuminate\Http\Request;
use function Laravel\Prompts\select;

class GetClassesController extends Controller
{
    public function index()
    {
        $classes = DB::table('classes')
        ->where('deleted', 1)
        ->select('id', 'name', 'type', 'subject', 'created_at', 'updated_at')
        ->get();
        return response()->json([
            'data' =>$classes
        ]);
    }
    public function show($user_id)
    {
        $classes = Classes::where('user_id', $user_id)
                        ->where('deleted', 0)
                        ->get();
    
        return response()->json($classes);
    }
    
}
