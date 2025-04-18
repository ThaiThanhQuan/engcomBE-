<?php

namespace App\Http\Controllers;

use App\Mail\ResetPasswordMail;
use Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Mail;

use Illuminate\Support\Str;
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


    public function sendResetToken(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);
        $user = DB::table('users')->where('email', $request->email)->first();
        if ($user) {
            $token = mt_rand(100000, 999999);
            $passwordReset = DB::table('password_resets')->where('email', $request->email)->first();
            if ($passwordReset) {
                DB::table('password_resets')->where('email', $request->email)->update([
                    'token' => $token,
                    'created_at' => now(),
                ]);
            } else {
                DB::table('password_resets')->insert([
                    'email' => $request->email,
                    'token' => $token,
                    'created_at' => now(),
                ]);
            }
            $link = $token;
            dd($user->uid); 
            if (is_null($user->uid)) {
                Mail::to($user->email)->send(new ResetPasswordMail($link));
            } else {
                return response()->json([
                    'message' => 'User is registered via web only, not using Google or Facebook.'
                ], 400);
            }
            return response()->json([
                'success' => true,
                'message' => 'Email has been sent with the reset code.',
                'data' => [
                        'link' => $link
                    ]
            ]);
        }
        return response()->json([
            'success' => false,
            'message' => 'User not found!',
        ], 404);
    }

    public function checktoken(Request $request)
    {
        $email = $request->input('email');
        $token = $request->input('token');
        $checktoken = DB::table('password_resets')->where('email', $email)->first();
        if ($checktoken) {
            if ($checktoken->token === $token) {
                return response()->json([
                    'message' => 'success',
                ]);
            } else {
                return response()->json([
                    'message' => "Code doesn't match",
                ], 400);
            }
        } else {
            return response()->json([
                'message' => 'No reset request found for this email',
            ], 404);
        }
    }

    public function resetpasswordwithToken(Request $request)
    {
        $request->validate([
            'password' => 'required|confirmed',
            'token' => 'required'
        ]);
        $token = $request->input('token');
        $passwordReset = DB::table('password_resets')->where('token', $token)->first();
        if (!$passwordReset) {
            return response()->json([
                'success' => false,
                'message' => 'xai token',
            ], 400);
        }
        $user = DB::table('users')->where('email', $passwordReset->email)->first();
        if ($user) {
            DB::table('users')->where('email', $passwordReset->email)->update([
                'password' => bcrypt($request->password),
                'updated_at' => now(),
            ]);
            DB::table('password_resets')->where('token', $token)->delete();
            return response()->json([
                'success' => true,
                'message' => 'Password has been reset!',
            ]);
        }
        return response()->json([
            'success' => false,
            'message' => 'User not found!',
        ], 404);
    }

    public function changepassWebUser(Request $request)
    {
        $userid = $request->input('userid');
        $password = $request->input('password');
        $user = DB::table('users')->where('id', $userid)->first();
        if ($user) {
            DB::table('users')->where('id', $userid)->update([
                'password' => Hash::make($password)
            ]);

        }
        return response()->json([
            'message' => 'Change password success',
        ]);
    }
}
