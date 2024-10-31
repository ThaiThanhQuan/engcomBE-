<?php

namespace App\Http\Controllers;

use App\Models\Classes;
use App\Models\Subscribe;
use DB;
use Illuminate\Http\Request;

class TopRankController extends Controller
{
    public function subscribe(string $type)
    {
        if ($type === 'subscribe') {
            // Đếm số lượng đăng ký của giáo viên và lấy thông tin giáo viên
            $Subscribes = Subscribe::join('classes', 'subscribe.class_id', '=', 'classes.id')
                ->join('users', 'classes.user_id', '=', 'users.id') // Nối với bảng users qua user_id trong classes
                ->where('users.deleted', 1) // Chỉ lấy người dùng không bị xóa
                ->where('classes.deleted', 1) // Chỉ lấy lớp không bị xóa
                ->select(
                    'users.id as user_id',
                    'users.name',
                    'users.avatar',
                    'users.role_id',
                    'users.created_at',
                    DB::raw('COUNT(subscribe.user_id) as rank_count')
                )
                ->groupBy('users.id', 'users.name', 'users.avatar', 'users.role_id', 'users.created_at')
                ->orderBy('rank_count', 'desc')
                ->get();
        } else {
            $Subscribes = []; 
        }
    
        return response()->json($Subscribes);
    }
    
    

    public function classes(string $type)
    {
        if ($type == 'classes') {
            $Classes = Classes::join('users', 'classes.user_id', '=', 'users.id')
                ->where('classes.deleted', 1)
                ->where('users.deleted', 1)
                ->select(
                    'users.id as user_id',
                    'users.name',
                    'users.avatar',
                    'users.role_id',
                    'users.created_at',
                    DB::raw('COUNT(classes.user_id) as rank_count')
                )
                ->groupBy('users.id', 'users.name', 'users.avatar', 'users.role_id', 'users.created_at')
                ->orderBy('rank_count', 'desc')
                ->limit(10)
                ->get();
        }
        return response()->json($Classes);
    }
}