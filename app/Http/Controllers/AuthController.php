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
            return response()->json(['error' => 'register that bai'], 409);
        }


        //tao token
        $token = JWTAuth::fromUser($user);
        return response()->json([
            'message' => 'register thanh cong',
            'user' => $user,
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
        return $this->respondWithToken($token);
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
        // Nhận access_token, uid và provider từ frontend gửi lên
        $token = $request->input('token');
        $provider = $request->input('provider');

        try {
            // Xác thực người dùng với access_token thông qua Socialite
            $user = Socialite::driver($provider)->stateless()->userFromToken($token);
            // Lấy email và name từ user
            $email = $user->getEmail();
            $name = $user->getname();
            $uid = $user->getid();

            // Kiểm tra xem người dùng đã tồn tại trong DB chưa dựa trên email hoặc uid
            $existingUser = User::where('email', $email)->orWhere('uid', $uid)->first();
            
            if ($existingUser) {
                // Người dùng đã tồn tại, trả về thông tin người dùng
                return response()->json([
                    $tokenA = JWTAuth::fromUser($existingUser),
                    'message' => 'User already exists',
                    'uid' => $existingUser->uid,
                    'email' => $existingUser->email,
                    'name' => $existingUser->name,
                    
                ]);
            } else {
                // Người dùng mới, lưu thông tin vào DB
                $newUser = User::create([
                    'uid' => $uid,
                    'email' => $email,
                    'name' => $name,
                    'token' => $token,
                ]);
                
                // Trả về uid và các thông tin khác
                
                
                return response()->json([
                    'message' => 'New user created',
                    'uid' => $newUser->uid,
                    'email' => $newUser->email,
                    'name' => $newUser->name,
                    $tokenA = JWTAuth::fromUser($newUser),
                    
                ]);
            }
        } catch (Exception $e) {
            return response()->json(['error' => 'Invalid access token or provider: ' . $e->getMessage()], 400);
        }
    }



}



    

