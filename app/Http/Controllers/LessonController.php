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
        $lesson = new Lesson();
        $lesson->type = $request->type;
        $lesson->course_id = $request->course_id;
        $lesson->name = $request->name;
        $lesson->save();

        // Trả về phản hồi sau khi lưu
        return response()->json([
            'message' => 'Lesson created successfully',
            'data' => $lesson
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Tìm kiếm bài học theo ID
        $lesson = Lesson::find($id);

        // Kiểm tra xem bài học có tồn tại không
        if (!$lesson) {
            return response()->json([
                'message' => 'Lesson not found.'
            ], 404);
        }

        // Trả về dữ liệu bài học
        return response()->json([
            'message' => 'Lesson retrieved successfully.',
            'data' => $lesson
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Tìm kiếm bài học theo ID
        $lesson = Lesson::find($id);

        // Kiểm tra xem bài học có tồn tại không
        if (!$lesson) {
            return response()->json([
                'message' => 'Lesson not found.'
            ], 404);
        }

        // Cập nhật thông tin từ request
        $lesson->type = $request->input('type', $lesson->type); // Cập nhật type, giữ nguyên nếu không có
        $lesson->name = $request->input('name', $lesson->name); // Cập nhật name, giữ nguyên nếu không có
        $lesson->course_id = $request->input('course_id', $lesson->course_id);

        // Lưu thay đổi vào cơ sở dữ liệu
        $lesson->save();

        // Trả về dữ liệu bài học đã được cập nhật
        return response()->json([
            'message' => 'Lesson updated successfully.',
            'data' => $lesson
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // Tìm lesson theo ID cùng với các mối quan hệ cần thiết
        $lesson = Lesson::with(['videos', 'lessonText', 'lessonExercises'])->find($id);

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
                $exercise->delete();
            }

            // Cuối cùng xóa lesson
            $lesson->delete();

            return response()->json(['message' => 'Lesson and all related content deleted successfully.'], 200);
        }

        return response()->json(['message' => 'Lesson not found.'], 404);
    }




}
