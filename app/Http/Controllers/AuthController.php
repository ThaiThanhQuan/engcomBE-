<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Laravel\Socialite\Facades\Socialite;
use Exception;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login','logout', 'register', 'getSocialUser', 'updateRole', 'refresh']]);
    }
    public function register(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|string|email|',
            'password' => 'required|string|confirmed|min:3',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'field' => 'password_confirmation',
                'error' => 'confirm password khong trung nhau',
            ], 422);
        }

        try {
            $user = User::create([
                'name' => $request->get('name'),
                'email' => $request->get('email'),
                'password' => Hash::make($request->get('password')),
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => 'email da ton tai', 'field' => 'email',], 409);
        }
        $userdata = User::find($user->id);
        // tao token
        $token = JWTAuth::fromUser($user);
        return response()->json([
            'message' => 'register thanh cong',
            'user' => [
                'id' => $userdata->id,
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
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);

    }
    public function login()
    {
        $credentials = request(['email', 'password']);

        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Tai khoan hoac mat khau khong chinh xac'], 401);
        }
        $user = auth()->user();
       
        $refreshToken =  $this->createRefreshToken();

        return response()->json([
            'access_token' => $token,
            'refresh_token' => $refreshToken,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'phonenumber' => $user->phonenumber,
                'email' => $user->email,
                'address' => $user->address,
                'sex' => $user->sex,
                'role_id' => $user->role_id,
                'avatar' => $user->avatar,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at
            ],
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
    public function profile()
    {
        try{
            return response()->json(auth('api')->user());
        }catch (JWTException $e){
            return response()->json(['error' => 'Unauthorized'],401);
        }
    }
    public function logout()
    {
        auth('api')->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }
    public function refresh()
    {
        $refreshToken = request()->refresh_token;
        try{
            $decoded = JWTAuth::getJWTProvider()->decode($refreshToken);
            // Xử lý cấp lại token mới
            // -> Lấy thông tin user
            $user = User::find($decoded['user_id']);
            if(! $user){
                return response()->json(['error' => "User not found"],404);
            }
            // auth('api')->invalidate(); // Vô hiệu hóa token hiện tại
            $token = auth('api')->login($user); //Tạo token mới
            $refreshToken = $this->createRefreshToken();
            return $this->respondWithToken($token,$refreshToken);
            // return response()->json($user);
        }catch(JWTException $e){
            return response()->json(['error' => 'Refresh Token Invalid'], 500);
        }
        
        // return $this->respondWithToken(auth('api')->refresh());
    }
    private function respondWithToken($token, $refreshToken)
    {
        return response()->json([
            'access_token' => $token,
            'refresh_token' => $refreshToken,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ]);
    }
    private function createRefreshToken(){
        $data = [
            'user_id' => auth('api')->user()->id,
            'random' => rand() . time(),
            'exp' => time() + config('jwt.refresh_ttl')
        ];
        $refreshToken =  JWTAuth::getJWTProvider()->encode($data);
        return $refreshToken;
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
                $token = JWTAuth::fromUser($existingUser);
                return response()->json([
                    'message' => 'User already exists',
                    'access_token' => $token,
                    'status' => '1',
                    'user' => [
                        'id' => $existingUser->id,
                        'name' => $existingUser->name,
                        'phonenumber' => $existingUser->phonenumber,
                        'email' => $existingUser->email,
                        'address' => $existingUser->address,
                        'sex' => $existingUser->sex,
                        'role_id' => $existingUser->role_id,
                        'avatar' => $existingUser->avatar,
                        'created_at' => $existingUser->created_at,
                        'updated_at' => $existingUser->updated_at
                    ],
                    'expires_in' => auth()->factory()->getTTL() * 60
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
                        'id' => $newUser->id,
                        'name' => $newUser->name,
                        'phonenumber' => $newUser->phonenumber,
                        'email' => $newUser->email,
                        'address' => $newUser->address,
                        'sex' => $newUser->sex,
                        'role_id' => 1,
                        'avatar' => $newUser->avatar,
                        'created_at' => $newUser->created_at,
                        'updated_at' => $newUser->updated_at
                    ],
                    'expires_in' => auth()->factory()->getTTL() * 60
                ]);
            }
        } catch (Exception $e) {
            return response()->json(['error' => 'Error processing request: ' . $e->getMessage()], 400);
        }
    }
    public function updateRole(Request $request)
    {
        // Nhận uid và role_id mới từ request
        $id = $request->input('id');
        $newRoleId = $request->input('role_id');

        // Kiểm tra xem id có được cung cấp hay không
        if (empty($id)) {
            return response()->json(['error' => 'id is required'], 400);
        }

        // Kiểm tra xem role_id có được cung cấp hay không
        if (empty($newRoleId)) {
            return response()->json(['error' => 'New role ID is required'], 400);
        }

        try {
            // Tìm người dùng theo id
            $user = User::where('id', $id)->first();

            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }

            // Cập nhật role_id cho người dùng
            $user->role_id = $newRoleId;
            $user->save();

            return response()->json([
                'message' => 'Role updated successfully',
                'role_id' => $user->role_id
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => 'Error processing request: ' . $e->getMessage()], 400);
        }
    }
}