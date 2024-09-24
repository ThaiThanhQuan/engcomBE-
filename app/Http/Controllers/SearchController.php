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
    
        if ($type === 'less') {
            $users = $usersQuery->limit(4)->get(); // Giới hạn kết quả nếu type là 'less'
        } else {
            $users = $usersQuery->paginate(10); // Phân trang nếu type là 'more', 10 kết quả mỗi trang
        }
    
        // Tìm kiếm classes
        $classesQuery = Classes::where('name', 'LIKE', "%{$query}%");
    
        if ($type === 'less') {
            $classes = $classesQuery->limit(4)->get(); // Giới hạn kết quả nếu type là 'less'
        } else {
            $classes = $classesQuery->paginate(10); // Phân trang nếu type là 'more'
        }
    
        // Trả về response dạng JSON với kết quả tìm kiếm users và classes
        return response()->json([
            [
                'type' => 'users',
                'children' => $type === 'more' ? $users->items() : $users,
                'pagination' => $type === 'more' ? [
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total(),
                ] : null
            ],
            [
                'type' => 'classes',
                'children' => $type === 'more' ? $classes->items() : $classes,
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
