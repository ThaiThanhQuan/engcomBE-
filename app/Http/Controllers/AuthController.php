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

        $this->middleware('auth:api', ['except' => ['login', 'register']]);
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

}}

    

