<?php

namespace App\Http\Controllers;

use App\Models\ExerciseOption;
use App\Models\Lesson;
use App\Models\LessonExercise;
use App\Models\LessonText;
use App\Models\LessonVideo;
use Illuminate\Http\Request;

class LessonContentController extends Controller
{
    public function store(Request $request)
    {
        $lesson = Lesson::find($request->lesson_id);
        $lessonType = $lesson->type;
        if ($lessonType == "0") {
            $video = new LessonVideo();
            $video->text = $request->text;
            $video->title = $request->title;
            $video->lesson_id = $lesson->id;
            $video->save();
            return response()->json([
                'message' => 'Video created successfully',
                'data' => $video
            ], 201);
        } else if ($lessonType == "1") {
            $textLesson = new LessonText();
            $textLesson->text = $request->text;
            $textLesson->title = $request->title;
            $textLesson->lesson_id = $lesson->id;
            $textLesson->save();
            return response()->json([
                'message' => 'Text lesson created successfully',
                'data' => $textLesson
            ], 201);
        } else if ($lessonType == "2") {
            $exercise = new LessonExercise();
            $exercise->title = $request->title;
            $exercise->text = $request->text;
            $exercise->lesson_id = $lesson->id;
            $exercise->save();
            if ($request->has('questions')) {
                foreach ($request->questions as $option) {
                    $exerciseOption = new ExerciseOption();
                    $exerciseOption->lesson_exercise_id = $exercise->id;
                    $exerciseOption->text = $option['text'];
                    $exerciseOption->is_correct = $option['is_correct'];
                    $exerciseOption->save();
                    $exercise_Option[] = $exerciseOption;
                }
            }
            return response()->json([
                'message' => 'Exercise and options created successfully',
                'data' => $exercise,
                'Option' => $exercise_Option
            ], 201);
        } else {
            return response()->json([
                'message' => 'Invalid lesson type'
            ], 400);
        }
    }

    public function show(string $id)
    {
        $lesson = Lesson::find($id);
        $lessonType = null;
        switch ($lesson->type) {
            case 0:
                $lessonType = LessonVideo::where('lesson_id', $lesson->id)->first();
                break;
            case 1:
                $lessonType = LessonText::where('lesson_id', $lesson->id)->first();
                break;
            case 2:
                $lessonType = LessonExercise::where('lesson_id', $lesson->id)->first();
                break;
            default:
                return response()->json(['message' => 'Invalid lesson type'], 400);
        }
        if (!$lesson) {
            return response()->json([
                'message' => 'Lesson not found'
            ], 404);
        }
        return response()->json([
            'data' => [
                'lesson' => [
                    'id' => $lesson->id,
                    'type' => $lesson->type,
                    'name' => $lesson->name,
                    'course_id' => $lesson->course_id,
                ],
                'type_data' => [
                    'id' => $lessonType->id,
                    'text' => $lessonType->text,
                    'title' => $lessonType->title,
                    'lesson_id' => $lessonType->lesson_id,
                ],
            ],
            'message' => 'Lesson retrieved successfully'
        ], 200);
    }

    public function update(Request $request, string $id)
    {
        $lesson = Lesson::find($id);
        if (!$lesson) {
            return response()->json(['message' => 'Lesson not found'], 404);
        }
        $lessonType = $lesson->type;
        $updatedData = null;
        switch ($lessonType) {
            case 0:
                $lessonVideo = LessonVideo::where('lesson_id', $lesson->id)->first();
                if ($lessonVideo) {
                    $lessonVideo->text = $request->text;
                    $lessonVideo->title = $request->title;
                    $lessonVideo->save();
                    $updatedData = $lessonVideo;
                } else {
                    return response()->json(['message' => 'Video not found'], 404);
                }
                break;
            case 1:
                $lessonText = LessonText::where('lesson_id', $lesson->id)->first();
                if ($lessonText) {
                    $lessonText->text = $request->text;
                    $lessonText->title = $request->title;
                    $lessonText->save();
                    $updatedData = $lessonText;
                } else {
                    return response()->json(['message' => 'Text lesson not found'], 404);
                }
                break;
            case 2:
                $lessonExercise = LessonExercise::where('lesson_id', $lesson->id)->first();
                if ($lessonExercise) {
                    $lessonExercise->title = $request->title;
                    $lessonExercise->text = $request->text;
                    $lessonExercise->save();
                    $updatedData = $lessonExercise;
                    if ($request->has('options')) {
                        foreach ($request->options as $option) {
                            $exerciseOption = ExerciseOption::where('lesson_exercise_id', $lessonExercise->id)
                                ->where('id', $option['id'] ?? null)
                                ->first();
                            if ($exerciseOption) {
                                $exerciseOption->text = $option['text'];
                                $exerciseOption->is_correct = $option['is_correct'];
                                $exerciseOption->save();
                            } else {
                                $newOption = new ExerciseOption();
                                $newOption->lesson_exercise_id = $lessonExercise->id;
                                $newOption->text = $option['text'];
                                $newOption->is_correct = $option['is_correct'];
                                $newOption->save();
                            }
                        }
                    }
                } else {
                    return response()->json(['message' => 'Exercise not found'], 404);
                }
                break;
            default:
                return response()->json(['message' => 'Invalid lesson type'], 400);
        }
        return response()->json([
            'message' => 'Lesson updated successfully',
            'data' => $updatedData
        ], 200);
    }

    public function destroy(string $id)
    {
        $lesson = Lesson::find($id);
        if (!$lesson) {
            return response()->json(['message' => 'Lesson not found'], 404);
        }
        $lessonType = $lesson->type;
        switch ($lessonType) {
            case 0:
                $lessonVideo = LessonVideo::where('lesson_id', $lesson->id)->first();
                if ($lessonVideo) {
                    $lessonVideo->delete();
                }
                break;
            case 1:
                $lessonText = LessonText::where('lesson_id', $lesson->id)->first();
                if ($lessonText) {
                    $lessonText->delete();
                }
                break;
            case 2:
                $lessonExercise = LessonExercise::where('lesson_id', $lesson->id)->first();
                if ($lessonExercise) {
                    ExerciseOption::where('lesson_exercise_id', $lessonExercise->id)->delete();
                    $lessonExercise->delete();
                }
                break;
            default:
                return response()->json(['message' => 'Invalid lesson type'], 400);
        }
        return response()->json(['message' => 'Associated data deleted successfully'], 200);
    }
}
