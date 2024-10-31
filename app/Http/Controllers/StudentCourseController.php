<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentCourseController extends Controller
{
    public function index(Request $req)
    {
        $class_id = $req->c;
        $user_id = $req->u;
        $courses = DB::table('course')
            ->where('class_id', $class_id)
            ->get();
        $result = [];
        foreach ($courses as $course) {
            $courseContent = [
                'id' => $course->id,
                'class_id' => $course->class_id,
                'name' => $course->name,
                'lessons' => []
            ];
            $lessons = DB::table('lesson')
                ->where('course_id', $course->id)
                ->get();
            foreach ($lessons as $lesson) {
                $lessonContent = [
                    'id' => $lesson->id,
                    'type' => $lesson->type,
                    'name' => $lesson->name,
                    'course_id' => $lesson->course_id,
                    'content' => []
                ];
                if ($lesson->type == 0) {
                    $videoContent = DB::table('lesson_video')
                        ->where('lesson_id', $lesson->id)
                        ->first();
                    if ($videoContent) {
                        $lessonContent['content'] = [
                            'id' => $videoContent->id,
                            'title' => $videoContent->title,
                            'text' => $videoContent->text,
                            'lesson_id' => $lesson->id
                        ];
                    }
                } elseif ($lesson->type == 1) { // Text
                    $textContent = DB::table('lesson_text')
                        ->where('lesson_id', $lesson->id)
                        ->first();
                    if ($textContent) {
                        $lessonContent['content'] = [
                            'text' => $textContent->text,
                            'lesson_id' => $lesson->id
                        ];
                    }
                } elseif ($lesson->type == 2) {
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
                                'text' => $question->text,
                                'is_correct' => $question->is_correct,
                            ];
                        }
                        $lessonContent['content'] = [
                            'id' => $exerciseContent->id,
                            'title' => $exerciseContent->title,
                            'text' => $exerciseContent->text,
                            'lesson_id' => $lesson->id,
                            'questions' => $questionArray
                        ];
                    }
                }
                $progress = DB::table('progress')
                    ->where('user_id', $user_id)
                    ->where('lesson_id', $lesson->id)
                    ->first();
                $lessonContent['is_completed'] = $progress ? (bool) $progress->is_completed : false;
                $lessonContent['is_in_progress'] = $progress && !$progress->is_completed;
                $courseContent['lessons'][] = $lessonContent;
            }
            $result[] = $courseContent;
        }
        return response()->json([
            'message' => 'Data retrieved successfully',
            'data' => $result
        ]);
    }
}
