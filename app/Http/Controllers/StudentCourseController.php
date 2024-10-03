<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentCourseController extends Controller
{
    public function index(Request $req) {
        $class_id = $req->c;
        $user_id = $req->u;
    
        // Truy vấn thông tin khóa học
        $courses = DB::table('course')
            ->where('class_id', $class_id)
            ->get();
    
        $result = [];
        foreach ($courses as $course) {
            // Khởi tạo thông tin khóa học
            $courseContent = [
                'id' => $course->id,
                'class_id' => $course->class_id,
                'name' => $course->name,
                'lessons' => []
            ];
    
            // Lấy các bài học cho khóa học
            $lessons = DB::table('lesson')
                ->where('course_id', $course->id)
                ->get();
    
            foreach ($lessons as $lesson) {
                // Khởi tạo thông tin bài học
                $lessonContent = [
                    'id' => $lesson->id,
                    'type' => $lesson->type,
                    'name' => $lesson->name,
                    'course_id' => $lesson->course_id,
                    'content' => [] // Khởi tạo mảng cho nội dung
                ];
    
                // Lấy nội dung cho bài học
                if ($lesson->type === 0) { // Video
                    $videoContent = DB::table('lesson_video')
                        ->where('lesson_id', $lesson->id)
                        ->first();
                    if ($videoContent) {
                        $lessonContent['content'] = [
                            'id' => $videoContent->id,
                            'video' => $videoContent->video,
                            'content' => $videoContent->content,
                            'lesson_id' => $lesson->id
                        ];
                    }
                } elseif ($lesson->type === 1) { // Text
                    $textContent = DB::table('lesson_text')
                        ->where('lesson_id', $lesson->id)
                        ->first();
                    if ($textContent) {
                        $lessonContent['content'] = [
                            'text' => $textContent->content,
                            'lesson_id' => $lesson->id
                        ];
                    }
                } elseif ($lesson->type === 2) { // Exercise
                    $exerciseContent = DB::table('lesson_exercise')
                        ->where('lesson_id', $lesson->id)
                        ->first();
                    if ($exerciseContent) {
                        $questions = DB::table('exercise_options')
                            ->where('lesson_exercise_id', $exerciseContent->id)
                            ->get();
    
                        $questionArray = [];
                        foreach ($questions as $question) {
                            $questionArray[] = [
                                'name' => $question->text,
                                'is_correct' => $question->is_correct,
                            ];
                        }
    
                        $lessonContent['content'] = [
                            'id' => $exerciseContent->id,
                            'title' => $exerciseContent->title,
                            'content' => $exerciseContent->content,
                            'lesson_id' => $lesson->id,
                            'questions' => $questionArray
                        ];
                    }
                }
    
                // Kiểm tra tiến trình của người dùng
                $progress = DB::table('progress')
                    ->where('user_id', $user_id)
                    ->where('lesson_id', $lesson->id)
                    ->first();
    
                // Nếu không có tiến trình, mặc định là false
                $lessonContent['is_completed'] = $progress ? (bool)$progress->is_completed : false;
    
                // Kiểm tra xem người dùng đang làm bài học chưa hoàn thành hay không
                $lessonContent['is_in_progress'] = $progress && !$progress->is_completed;
    
                // Thêm bài học vào khóa học
                $courseContent['lessons'][] = $lessonContent;
            }
    
            // Thêm khóa học vào kết quả
            $result[] = $courseContent;
        }
    
        // Trả về dữ liệu
        return response()->json([
            'message' => 'Data retrieved successfully',
            'data' => $result
        ]);
    }
    

    
    
    
}
