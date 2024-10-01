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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $course = Course::findOrFail($id);

        return response()->json([
            'message' => 'success',
            'data' => $course
        ]);
    }
    public function ownShow(string $class_id)
    {
        // Lấy các khóa học
        $courses = Course::where('class_id', $class_id)->get()->toArray();
    
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
                        'video' => $video->video,
                        'content' => $video->content,
                        'lesson_id' => $lesson->id,
                    ];
                }
    
                // Thêm lesson text nếu có
                foreach ($lesson->lessonText as $text) {
                    $content[] = [
                        'text' => $text->content,
                        'lesson_id' => $lesson->id,
                    ];
                }
    
                // Thêm exercise options nếu có
                foreach ($lesson->lessonExercises as $exercise) {
                    $questions = [];
                    foreach ($exercise->exerciseOptions as $option) {
                        $questions[] = [
                            'name' => $option->text,
                            'is_correct' => $option->is_correct,
                        ];
                    }
    
                    // Chỉ thêm bài tập nếu có câu hỏi
                    if (!empty($questions)) {
                        $content[] = [
                            'id' => $exercise->id,
                            'title' => $exercise->title,
                            'content' => $exercise->content, // Hoặc một trường nào đó mà bạn muốn
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
                'content' => array_filter($content),  // Chỉ chứa nội dung có giá trị
            ]
        ]);
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
    public function destroy(string $id)
    {
        //
    }
    
}
