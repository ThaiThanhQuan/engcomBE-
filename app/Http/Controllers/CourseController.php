<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\Request;

class CourseController extends Controller
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
        // Tạo mới một đối tượng Course
        $course = new Course();
        $course->class_id = $request->class_id; // Gán class_id
        $course->name = $request->name; // Gán tên từ request
        // Lưu vào cơ sở dữ liệu
        $course->save();

        // Trả về phản hồi sau khi lưu
        return response()->json([
            'message' => 'Course created successfully',
            'data' => $course
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $course = Course::findOrFail($id);
        if ($course->deleted == 0) {
            return response()->json([
                'message' => 'Course is deleted and cannot be accessed.',
            ], 403);
        }
        return response()->json([
            'message' => 'success',
            'data' => $course
        ]);
    }
    public function ownShow(string $class_id)
    {
        // Lấy các khóa học mà deleted = 1
        $courses = Course::where('class_id', $class_id)
            ->where('deleted', 1) // Thay đổi ở đây
            ->get();
    
        // Khởi tạo mảng để lưu bài học và nội dung
        $lessons = [];
        $content = [];
    
        foreach ($courses as $course) {
            // Lấy bài học tương ứng với khóa học
            $courseLessons = Lesson::with(['videos', 'lessonText', 'lessonExercises.exerciseOptions'])
                ->where('course_id', $course['id'])
                ->get();
    
            foreach ($courseLessons as $lesson) {
                $lessons[] = $lesson->only(['id', 'type', 'name', 'course_id']);
    
                // Thêm video vào content nếu có
                foreach ($lesson->videos as $video) {
                    $content[] = [
                        'id' => $video->id,
                        'title' => $video->title,
                        'text' => $video->text,
                        'lesson_id' => $lesson->id,
                    ];
                }
    
                // Thêm lesson text nếu có
                foreach ($lesson->lessonText as $text) {
                    $content[] = [
                        'id' => $text->id,
                        'text' => $text->text,
                        'lesson_id' => $lesson->id,
                    ];
                }
    
                // Thêm exercise options nếu có
                foreach ($lesson->lessonExercises as $exercise) {
                    $questions = [];
                    foreach ($exercise->exerciseOptions as $option) {
                        $questions[] = [
                            'text' => $option->text,
                            'is_correct' => $option->is_correct,
                        ];
                    }
    
                    // Chỉ thêm bài tập nếu có câu hỏi
                    if (!empty($questions)) {
                        $content[] = [
                            'id' => $exercise->id,
                            'title' => $exercise->title,
                            'text' => $exercise->text,
                            'lesson_id' => $lesson->id,
                            'questions' => $questions,
                        ];
                    }
                }
            }
        }
    
        return response()->json([
            'data' => [
                'courses' => $courses,
                'lessons' => $lessons,
                'content' => array_filter($content),  
            ]
        ]);
    }
    


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        // Tìm khóa học theo ID
        $course = Course::findOrFail($id);

        // Cập nhật thông tin khóa học
        $course->class_id = $request->input('class_id', $course->class_id); 
        $course->name = $request->input('name', $course->name); // Gán tên từ request, nếu không có thì giữ nguyên
        // Lưu vào cơ sở dữ liệu
        $course->save();

        // Trả về phản hồi sau khi cập nhật
        return response()->json([
            'message' => 'Course updated successfully',
            'data' => $course
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $course_id)
    {

        // Tìm bản ghi với id tương ứng
        $course = Course::where('id', $course_id)->first();

        if ($course) {
            $course->deleted = 0;
            $course->save();

            return response()->json(['message' => 'course has been marked as not deleted.']);
        }

        return response()->json(['message' => 'course not found.'], 404);
    }

}
