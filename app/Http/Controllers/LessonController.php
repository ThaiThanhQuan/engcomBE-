<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use Illuminate\Http\Request;

class LessonController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // Tìm lesson theo ID cùng với các mối quan hệ cần thiết
        $lesson = Lesson::with(['videos', 'lessonText', 'lessonExercises.exerciseOptions'])->find($id);
    
        if ($lesson) {
            // Xóa tất cả videos liên quan
            foreach ($lesson->videos as $video) {
                $video->delete();
            }
    
            // Xóa lessonText nếu có
            if ($lesson->lessonText) {
                foreach ($lesson->lessonText as $lessonText) {
                    $lessonText->delete();
                }
            }
    
            // Xóa lessonExercises và các exerciseOptions liên quan
            foreach ($lesson->lessonExercises as $exercise) {
                // Xóa exerciseOptions
                foreach ($exercise->exerciseOptions as $option) {
                    $option->delete();
                }
                // Xóa lessonExercise
                foreach ($exercise->lessonExercises as $exercise) {
                    $exercise->delete();
                }
            }
    
            // Cuối cùng xóa lesson
            $lesson->delete();
    
            return response()->json(['message' => 'Lesson and all related content deleted successfully.'], 200);
        }
    
        return response()->json(['message' => 'Lesson not found.'], 404);
    }
    
    
    
    
}
