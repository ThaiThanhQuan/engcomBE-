<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Classes;

class SearchController extends Controller
{
    public function search(Request $request)
{
    $query = $request->q;
    $type = $request->type;

    // Tìm kiếm users
    $usersQuery = User::where('name', 'LIKE', "%{$query}%");

    // Determine if we need to get users or paginate
    if ($type === 'less') {
        $users = $usersQuery->limit(4)->get(); // Get limited results for 'less'
    } else {
        $users = $usersQuery->paginate(10); // Paginate results for 'more'
    }

    // Tìm kiếm classes
    $classesQuery = Classes::where('name', 'LIKE', "%{$query}%");

    // Determine if we need to get classes or paginate
    if ($type === 'less') {
        $classes = $classesQuery->limit(4)->get(); // Get limited results for 'less'
    } else {
        $classes = $classesQuery->paginate(10); // Paginate results for 'more'
    }

    // Prepare response for users
    $usersResponse = $type === 'more' ? $users->items() : $users;

    // Prepare children for users
    $usersChildren = collect($usersResponse)->map(function ($user) {
        return ['name' => $user->name, 'banner' => $user->avatar]; // Prepare the response format
    });

    // Prepare response for classes
    $classesResponse = $type === 'more' ? $classes->items() : $classes;

    // Prepare children for classes
    $classesChildren = collect($classesResponse)->map(function ($class) {
        return ['title' => $class->name, 'banner' => $class->thumbnail]; // Prepare the response format
    });

    // Trả về response dạng JSON với kết quả tìm kiếm users và classes
    return response()->json([
        [
            'type' => 'users',
            'children' => $usersChildren,
            'pagination' => $type === 'more' ? [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ] : null
        ],
        [
            'type' => 'classes',
            'children' => $classesChildren,
            'pagination' => $type === 'more' ? [
                'current_page' => $classes->currentPage(),
                'last_page' => $classes->lastPage(),
                'per_page' => $classes->perPage(),
                'total' => $classes->total(),
            ] : null
        ],
    ]);
}

    

    
}
