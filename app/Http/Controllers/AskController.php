<?php

namespace App\Http\Controllers;

use App\Models\Ask;
use App\Models\User;
use Illuminate\Http\Request;

class AskController extends Controller
{
    public function store(Request $request)
    {
        $ask = Ask::create([
            'lesson_id' => $request->lesson_id,
            'user_id' => $request->user_id,
            'parent_id' => $request->parent_id,
            'content' => $request->content,
        ]);
        $user = User::find($ask->user_id);
        return response()->json([
            'message' => 'Ask created successfully',
            'data' => [
                'ask' => $ask,
                'user' => $user ? [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'avatar' => $user->avatar,
                ] : null,
            ],
        ], 201);
    }

    public function show(string $lesson_id)
    {
        $asks = Ask::where('lesson_id', $lesson_id)
            ->whereNull('parent_id')
            ->get();

        if ($asks->isEmpty()) {
            return response()->json(['message' => 'No asks found for this lesson'], 404);
        }
        $asksWithUserInfo = $asks->map(function ($ask) {
            $user = User::find($ask->user_id);
            return [
                'ask' => $ask,
                'user' => $user ? [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'avatar' => $user->avatar,
                ] : null,
            ];
        });
        return response()->json([
            'data' => $asksWithUserInfo,
        ]);
    }

    public function destroy(string $id)
    {
        $ask = Ask::find($id);
        if (!$ask) {
            return response()->json(['message' => 'Ask not found'], 404);
        }
        $deletedAsk = $ask->fresh();
        $ask->delete();
        $user = User::find($deletedAsk->user_id);
        return response()->json([
            'message' => 'Ask deleted successfully',
            'data' => [
                'ask' => $deletedAsk,
                'user' => $user ? [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'avatar' => $user->avatar,
                ] : null,
            ],
        ], 200);
    }

    public function reply(string $lesson_id)
    {
        $asks = Ask::where('lesson_id', $lesson_id)
            ->whereNotNull('parent_id')
            ->get();

        if ($asks->isEmpty()) {
            return response()->json(['message' => 'No asks found for this lesson'], 404);
        }
        $asksWithUserInfo = $asks->map(function ($ask) {
            $user = User::find($ask->user_id);

            return [
                'ask' => $ask,
                'user' => $user ? [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'avatar' => $user->avatar,
                ] : null,
            ];
        });
        return response()->json([
            'data' => $asksWithUserInfo,
        ]);
    }

}
