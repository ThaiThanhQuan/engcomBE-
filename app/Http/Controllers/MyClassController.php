<?php

namespace App\Http\Controllers;

use App\Models\Classes;
use App\Models\Subscribe;
use App\Models\User;
use Illuminate\Http\Request;

class MyClassController extends Controller
{
    public function show(string $user_id)
    {
        $user = User::find($user_id);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }
        $subscription = Subscribe::where('user_id', $user_id)->first();
        $class = null;
        if ($subscription) {
            $class = Classes::find($subscription->class_id);
        }
        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'class' => $class,
            ],
        ], 200);
    }
}
