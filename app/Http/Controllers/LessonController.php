<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use Illuminate\Http\Request;

class LessonController extends Controller
{
    public function store(Request $request)
    {
        $lesson = new Lesson();
        $lesson->type = $request->type;
        $lesson->course_id = $request->course_id;
        $lesson->name = $request->name;
        $lesson->save();
        return response()->json([
            'message' => 'Lesson created successfully',
            'data' => $lesson
        ], 201);
    }
    public function show(string $id)
    {
        $lesson = Lesson::find($id);
        if (!$lesson) {
            return response()->json([
                'message' => 'Lesson not found.'
            ], 404);
        }
        return response()->json([
            'message' => 'Lesson retrieved successfully.',
            'data' => $lesson
        ], 200);
    }

    public function update(Request $request, string $id)
    {
        $lesson = Lesson::find($id);
        if (!$lesson) {
            return response()->json([
                'message' => 'Lesson not found.'
            ], 404);
        }
        $lesson->type = $request->input('type', $lesson->type);
        $lesson->name = $request->input('name', $lesson->name);
        $lesson->course_id = $request->input('course_id', $lesson->course_id);
        $lesson->save();
        return response()->json([
            'message' => 'Lesson updated successfully.',
            'data' => $lesson
        ], 200);
    }

    public function destroy($id)
    {
        $lesson = Lesson::with(['videos', 'lessonText', 'lessonExercises'])->find($id);
        if ($lesson) {
            foreach ($lesson->videos as $video) {
                $video->delete();
            }
            if ($lesson->lessonText) {
                foreach ($lesson->lessonText as $lessonText) {
                    $lessonText->delete();
                }
            }
            foreach ($lesson->lessonExercises as $exercise) {
                foreach ($exercise->exerciseOptions as $option) {
                    $option->delete();
                }
                $exercise->delete();
            }
            $lesson->delete();

            return response()->json(['message' => 'Lesson and all related content deleted successfully.'], 200);
        }
        return response()->json(['message' => 'Lesson not found.'], 404);
    }
}
