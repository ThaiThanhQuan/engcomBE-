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
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'logout', 'refresh', 'register', 'getSocialUser', 'updateRole', 'refresh']]);
    }

    public function register(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|string|email|',
            'password' => 'required|string|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'field' => 'password_confirmation',
                'error' => 'confirm password does not match.',
            ], 422);
        }
        try {
            $user = User::create([
                'name' => $request->get('name'),
                'email' => $request->get('email'),
                'password' => Hash::make($request->get('password')),
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => 'email already exists.', 'field' => 'email',], 409);
        }
        $refreshToken = $this->createRefreshToken();

        $userdata = User::find($user->id);
        $token = JWTAuth::fromUser($user);
        return response()->json([
            'message' => 'register thanh cong',
            'refresh_token' => $refreshToken,
            'user' => [
                'id' => $userdata->id,
                'uid' => $userdata->uid,
                'name' => $userdata->name,
                'phone_number' => $userdata->phone_number,
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
            return response()->json(['error' => 'Account or password is incorrect.'], 401);
        }
        $user = auth()->user();
        $refreshToken = $this->createRefreshToken();
        return response()->json([
            'access_token' => $token,
            'refresh_token' => $refreshToken,
            'user' => [
                'id' => $user->id,
                'uid' => $user->uid,
                'name' => $user->name,
                'phone_number' => $user->phone_number,
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
        try {
            return response()->json(auth('api')->user());
        } catch (JWTException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }
    public function logout()
    {
        auth('api')->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }
    public function refresh()
    {
        $token = request()->header('Authorization');
        try {
            $token = str_replace('Bearer ', '', $token);
            $jwtToken = new \Tymon\JWTAuth\Token($token);
            $decoded = JWTAuth::getJWTProvider()->decode($jwtToken);
            $user = null;
            if (isset($decoded['sub'])) {
                $user = User::find($decoded['sub']);
            }
            if (!$user && isset($decoded['uid'])) {
                $user = User::where('uid', $decoded['uid'])->first();
            }
            if (!$user) {
                return response()->json(['error' => "User not found"], 404);
            }
            $newToken = JWTAuth::fromUser($user);
            $refreshToken = $this->createRefreshToken();
            return $this->respondWithToken($newToken, $refreshToken);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Refresh Token Invalid'], 500);
        }
    }

    private function respondWithToken($token, $refreshToken)
    {
        return response()->json([
            'access_token' => $token,
            'refresh_token' => $refreshToken,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }

    public function getSocialUser(Request $request)
    {
        $uid = $request->input('uid');
        $email = $request->input('email');
        $name = $request->input('name');
        $avatar = $request->input('avatar');
        try {
            $existingUser = User::where('uid', $uid)->first();
            $refreshToken = $this->createRefreshToken();
            if ($existingUser) {
                $token = JWTAuth::fromUser($existingUser);
                return response()->json([
                    'message' => 'User already exists',
                    'access_token' => $token,
                    'refresh_token' => $refreshToken,
                    'status' => '1',
                    'user' => [
                        'id' => $existingUser->id,
                        'uid' => $existingUser->uid,
                        'name' => $existingUser->name,
                        'phone_number' => $existingUser->phone_number,
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
                $newUser = User::create([
                    'uid' => $uid,
                    'email' => $email,
                    'name' => $name,
                    'avatar' => $avatar,
                ]);
                $token = JWTAuth::fromUser($newUser);
                return response()->json([
                    'message' => 'New user created',
                    'access_token' => $token,
                    'refresh_token' => $refreshToken,
                    'status' => '2',
                    'user' => [
                        'id' => $newUser->id,
                        'uid' => $newUser->uid,
                        'name' => $newUser->name,
                        'phone_number' => $newUser->phone_number,
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
        $id = $request->input('id');
        $newRoleId = $request->input('role_id');
        if (empty($id)) {
            return response()->json(['error' => 'id is required'], 400);
        }
        if (empty($newRoleId)) {
            return response()->json(['error' => 'New role ID is required'], 400);
        }
        try {
            $user = User::where('id', $id)->first();
            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }
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

    private function createRefreshToken()
    {
        $data = [
            'random' => rand() . time(),
            'exp' => time() + config('jwt.refresh_ttl')
        ];
        $refreshToken = JWTAuth::getJWTProvider()->encode($data);
        return $refreshToken;
    }

    public function updateUser(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }
        if ($request->has('name')) {
            $user->name = $request->name;
        }
        if ($request->has('email')) {
            $user->email = $request->email;
        }
        if ($request->has('phone_number')) {
            $user->phone_number = $request->phone_number;
        }
        if ($request->has('gender')) {
            $user->sex = $request->gender;
        }
        if ($request->has('address')) {
            $user->address = $request->address;
        }
        $user->save();
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'phone_number' => $user->phone_number,
                'email' => $user->email,
                'address' => $user->address,
                'sex' => $user->sex,
                'role_id' => $user->role_id,
                'uid' => $user->uid,
                'token' => $user->token,
                'avatar' => $user->avatar,
            ],
        ], 200);
    }


    public function uploadAvatar(Request $request, $id)
    {
        $request->validate([
            'file' => 'required|file|mimes:jpg,png,jpeg,gif|max:2048',
        ]);
        $user = User::find($id);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('uploads', $fileName, 'public');
            $user->avatar = $fileName;
            $user->save();
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'phone_number' => $user->phone_number,
                    'email' => $user->email,
                    'address' => $user->address,
                    'sex' => $user->sex,
                    'role_id' => $user->role_id,
                    'uid' => $user->uid,
                    'token' => $user->token,
                    'avatar' => $user->avatar,
                ],
            ]);
        }
        return response()->json(['success' => false, 'message' => 'No file uploaded'], 400);
    }
}

