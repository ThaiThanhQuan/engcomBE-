<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Exception;

class AuthController extends Controller
{
    public function __construct()
    {


       

        $this->middleware('auth:api', ['except' => ['login', 'register', 'getSocialUser']]);

    }
    public function register(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|string|email|',
            'password' => 'required|string|confirmed|min:3',
        ]);


        try {
            $user = User::create([
                'name' => $request->get('name'),
                'email' => $request->get('email'),
                'password' => Hash::make($request->get('password')),
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => 'error'], 409);
        }
        $userdata = User::find($user->id);
        // tao token
        $token = JWTAuth::fromUser($user);
        return response()->json([
            'message' => 'register thanh cong',
            'user' => [
                'name' => $userdata->name,
                'phonenumber' => $userdata->phonenumber,
                'email' => $userdata->email,
                'address' => $userdata->address,
                'sex' => $userdata->sex,
                'role_id' => $userdata->role_id,
                'avatar' => $userdata->avatar,
                'created_at' => $userdata->created_at,
                'updated_at' => $userdata->updated_at
            ],
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 80
        ]);

    }
    public function login()
    {
        $credentials = request(['email', 'password']);

        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $user = auth()->user();
        return response()->json([
            'token' => $token,
            'user' => [
                'name' => $user->name,
                'phonenumber' => $user->phonenumber,
                'email' => $user->email,
                'address' => $user->address,
                'sex' => $user->sex,
                'role_id' => $user->role_id,
                'avatar' => $user->avatar,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at
            ]
        ]);
    }
    public function profile()
    {
        return response()->json(auth('api')->user());
    }
    public function logout()
    {
        auth('api')->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }
    public function refresh()
    {
        return $this->respondWithToken(auth('api')->refresh());
    }
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ]);




    }
    public function getSocialUser(Request $request)
    {
        // Nhận uid từ frontend gửi lên
        $uid = $request->input('uid');
        $email = $request->input('email');
        $name = $request->input('name');
        $avatar = $request->input('avatar');

        try {
            // Kiểm tra xem người dùng đã tồn tại trong DB chưa
            $existingUser = User::where('uid', $uid)->first();

            if ($existingUser) {
                return response()->json([
                    'message' => 'User already exists',
                    'status' => '1',
                    'user' => [
                        'uid' => $existingUser->uid,
                        'email' => $existingUser->email,
                        'name' => $existingUser->name,
                        'avatar' => $existingUser->avatar,                   
                    ]
                ]);
            } else {
                // Người dùng mới, lưu thông tin vào DB
                $newUser = User::create([
                    'uid' => $uid,
                    'email' => $email,
                    'name' => $name,
                    'avatar' => $avatar,
                ]);

                // Tạo token JWT cho người dùng mới
                $token = JWTAuth::fromUser($newUser);
                return response()->json([
                    'message' => 'New user created',
                    'access_token' => $token,
                    'status' => '2',
                    'user' => [
                        'uid' => $newUser->uid,
                        'email' => $newUser->email,
                        'name' => $newUser->name,
                        'avatar' => $newUser->avatar,
                    ]
                ]);
            }
        } catch (Exception $e) {
            return response()->json(['error' => 'Error processing request: ' . $e->getMessage()], 400);
        }
    }

}






