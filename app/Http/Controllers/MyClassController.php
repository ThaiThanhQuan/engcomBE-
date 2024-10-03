<?php

namespace App\Http\Controllers;

use App\Models\Classes;
use App\Models\Subscribe;
use App\Models\User;
use Illuminate\Http\Request;

class MyClassController extends Controller
{

    public function index()
    {
    }
    public function store(Request $request)
    {
    }
    public function show(string $user_id)
    {
        // Tìm người dùng theo user_id
        $user = User::find($user_id);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }

        // Lấy thông tin lớp đăng ký (subscription) của người dùng
        $subscription = Subscribe::where('user_id', $user_id)->first();

        // Kiểm tra nếu subscription tồn tại và lấy thông tin lớp
        $class = null;
        if ($subscription) {
            $class = Classes::find($subscription->class_id);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,          // Thông tin người dùng
                'class' => $class,        // Thông tin lớp (class)
            ],
        ], 200);
    }



    public function update(Request $request, string $id)
    {
    }
 function destroy(string $id)
    {
    }
}
