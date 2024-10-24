<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
class UserController extends Controller
{
    public function index()
    {
        $users = User::get();
        return response()->json($users);
    }
    public function store(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            $arr = [
                'success' => false,
                'message' => 'Thêm user thất bại! ',
                'data' => $validator->errors(),
            ];
            return response()->json($arr);
        }

        $input['password'] = bcrypt($input['password']); 

        $user = User::create($input);
        $arr = [
            'status' => true,
            'message' => "Thêm user thành công",
            'data' => $user
        ];

        return response()->json($arr, 201);
    }

    public function show(string $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy user',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $user
        ], 200); 
    }


    public function update(Request $request, string $id)
    {
        $user = User::where('role_id', 1)->find($id);
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email',
        ]);

        if ($validator->fails()) {
            $arr = [
                'success' => false,
                'message' => 'Cập nhập user không thành công',
                'data' => $validator->errors(),
            ];
            return response()->json($arr, 200);
        }

        $user->update($input);
        $arr = [
            'status' => true,
            'message' => 'Cập nhập user thành công',
            'data' => $user
        ];
        return response()->json($arr, 200);
    }

    public function destroy(string $id)
    {

        $user = User::find($id);




        $user->update(['deleted' => 0]);


        $arr = [
            'status' => true,
            'message' => 'Xóa thành công (đã cập nhật deleted = 0)',
            'data' => $user,
        ];

        return response()->json($arr, 200);
    }

}
