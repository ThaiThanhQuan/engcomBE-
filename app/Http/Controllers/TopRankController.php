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
        if ($type == 'subscribe') {
            $Subscribes = Subscribe::join('users', 'subscribe.user_id', '=', 'users.id')
                ->where('users.deleted', 1)
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
                ->limit(10)
                ->get();

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