<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Classes;
use App\Models\Comment;
use App\Models\Subscribe;

class SearchController extends Controller
{
    public function search(Request $request)
{
    $query = $request->q;
    $type = $request->type;

    // Tìm kiếm users
    $usersQuery = User::where('name', 'LIKE', "%{$query}%");
    
    if ($type === 'less') {
        $users = $usersQuery->limit(4)->get();
    } else {
        $users = $usersQuery->paginate(10);
    }

    // Tìm kiếm classes
    $classesQuery = Classes::where('name', 'LIKE', "%{$query}%");
    
    if ($type === 'less') {
        $classes = $classesQuery->limit(4)->get();
    } else {
        $classes = $classesQuery->paginate(10);
    }

    // Prepare response for users
    $usersResponse = $type === 'more' ? $users->items() : $users;

    // Prepare children for users
    $usersChildren = collect($usersResponse)->map(function ($user) {
        return [
            'user_id' => $user->id,
            'name' => $user->name,
            'img' => $user->avatar,
            'type' => $user->role_id
        ];
    });

    // Prepare response for classes
    $classesResponse = $type === 'more' ? $classes->items() : $classes;

    // Prepare children for classes
    $classesChildren = collect($classesResponse)->map(function ($class) use ($type) {
        // Thông tin lớp học
        $classData = [
            'class_id' => $class->id,
            'title' => $class->name,
            'thumbnail' => $class->thumbnail,
            'description' => $class->description
        ];
    
        // Biến để lưu thông tin người dùng
        $infoData = [];
    
        if ($type === 'more') {
            // Lấy thông tin người dùng chỉ khi type là 'more'
            $user = User::find($class->user_id);
            
            // Kiểm tra xem người dùng có tồn tại không
            if ($user) {
                $infoData = [
                    'user' => [
                        'user_id' => $user->id,
                        'name' => $user->name,
                        'avatar' => $user->avatar,
                    ],
                    'comment_count' => Comment::where('class_id', $class->id)->count(),
                    'subscribe_count' => Subscribe::where('class_id', $class->id)->count(),
                ];
            }
        }
    
        return [
            'class' => $classData,
            'info' => $infoData // Nếu không có thông tin người dùng, nó sẽ là một mảng rỗng
        ];
    });    

    // Trả về response dạng JSON
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
