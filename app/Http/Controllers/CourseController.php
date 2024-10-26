<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function store(Request $request)
    {
        $course = new Course();
        $course->class_id = $request->class_id;
        $course->name = $request->name;
        $course->save();
        return response()->json([
            'message' => 'Course created successfully',
            'data' => $course
        ], 201);
    }

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
        $courses = Course::where('class_id', $class_id)
            ->where('deleted', 1)
            ->get();
        $lessons = [];
        $content = [];
        foreach ($courses as $course) {
            $courseLessons = Lesson::with(['videos', 'lessonText', 'lessonExercises.exerciseOptions'])
                ->where('course_id', $course['id'])
                ->get();
            foreach ($courseLessons as $lesson) {
                $lessons[] = $lesson->only(['id', 'type', 'name', 'course_id']);
                foreach ($lesson->videos as $video) {
                    $content[] = [
                        'id' => $video->id,
                        'title' => $video->title,
                        'text' => $video->text,
                        'lesson_id' => $lesson->id,
                    ];
                }
                foreach ($lesson->lessonText as $text) {
                    $content[] = [
                        'id' => $text->id,
                        'text' => $text->text,
                        'lesson_id' => $lesson->id,
                    ];
                }
                foreach ($lesson->lessonExercises as $exercise) {
                    $questions = [];
                    foreach ($exercise->exerciseOptions as $option) {
                        $questions[] = [
                            'text' => $option->text,
                            'is_correct' => $option->is_correct,
                        ];
                    }
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

    public function update(Request $request, string $id)
    {
        $course = Course::findOrFail($id);
        $course->class_id = $request->input('class_id', $course->class_id);
        $course->name = $request->input('name', $course->name);
        $course->save();
        return response()->json([
            'message' => 'Course updated successfully',
            'data' => $course
        ], 200);
    }

    public function destroy(string $course_id)
    {
        $course = Course::where('id', $course_id)->first();
        if ($course) {
            $course->deleted = 0;
            $course->save();
            return response()->json(['message' => 'course has been marked as not deleted.']);
        }
        return response()->json(['message' => 'course not found.'], 404);
    }
}
