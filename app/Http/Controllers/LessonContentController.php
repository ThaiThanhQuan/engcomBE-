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
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }
    public function store(Request $request)
    {
        // Tìm bài học theo ID
        $lesson = Lesson::find($request->lesson_id);
        $lessonType = $lesson->type;

        // Kiểm tra loại bài học và xử lý
        if ($lessonType == "0") {
            // Insert vào bảng lesson_video
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
            // Insert vào bảng lesson_text
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
            // Insert vào bảng lesson_exercise
            $exercise = new LessonExercise();
            $exercise->title = $request->title;
            $exercise->text = $request->text;
            $exercise->lesson_id = $lesson->id;
            $exercise->save();

            // Kiểm tra nếu có các tùy chọn (options) trong request
            if ($request->has('options')) {
                foreach ($request->options as $option) {
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

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Tìm bài học theo ID
        $lesson = Lesson::find($id);

        // Xác định loại bài học
        $lessonType = null;
        // Dựa trên giá trị type, xác định tên loại bài học
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
        // Kiểm tra xem bài học có tồn tại không
        if (!$lesson) {
            return response()->json([
                'message' => 'Lesson not found'
            ], 404);
        }

        // Trả về thông tin bài học
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
        // Tìm bài học theo ID
        $lesson = Lesson::find($id);
    
        // Kiểm tra xem bài học có tồn tại không
        if (!$lesson) {
            return response()->json(['message' => 'Lesson not found'], 404);
        }
    
        // Lấy type của bài học
        $lessonType = $lesson->type;
    
        // Khởi tạo biến để lưu dữ liệu đã cập nhật
        $updatedData = null;
    
        // Cập nhật dữ liệu dựa trên loại
        switch ($lessonType) {
            case 0: // Video
                $lessonVideo = LessonVideo::where('lesson_id', $lesson->id)->first();
                if ($lessonVideo) {
                    $lessonVideo->text = $request->text; // Cập nhật nội dung video
                    $lessonVideo->title = $request->title; // Cập nhật tiêu đề video
                    $lessonVideo->save(); // Lưu thay đổi
    
                    $updatedData = $lessonVideo; // Lưu dữ liệu đã cập nhật
                } else {
                    return response()->json(['message' => 'Video not found'], 404);
                }
                break;
    
            case 1: // Text
                $lessonText = LessonText::where('lesson_id', $lesson->id)->first();
                if ($lessonText) {
                    $lessonText->text = $request->text; // Cập nhật nội dung văn bản
                    $lessonText->title = $request->title; // Cập nhật tiêu đề văn bản
                    $lessonText->save(); // Lưu thay đổi
    
                    $updatedData = $lessonText; // Lưu dữ liệu đã cập nhật
                } else {
                    return response()->json(['message' => 'Text lesson not found'], 404);
                }
                break;
    
            case 2: // Exercise
                $lessonExercise = LessonExercise::where('lesson_id', $lesson->id)->first();
                if ($lessonExercise) {
                    $lessonExercise->title = $request->title; // Cập nhật tiêu đề bài tập
                    $lessonExercise->text = $request->text; // Cập nhật nội dung bài tập
                    $lessonExercise->save(); // Lưu thay đổi
    
                    $updatedData = $lessonExercise; // Lưu dữ liệu đã cập nhật
    
                    // Cập nhật các tùy chọn (options) nếu có trong request
                    if ($request->has('options')) {
                        foreach ($request->options as $option) {
                            // Kiểm tra xem tùy chọn đã tồn tại chưa
                            $exerciseOption = ExerciseOption::where('lesson_exercise_id', $lessonExercise->id)
                                ->where('id', $option['id'] ?? null)
                                ->first();
                            if ($exerciseOption) {
                                // Nếu tùy chọn tồn tại, cập nhật nó
                                $exerciseOption->text = $option['text'];
                                $exerciseOption->is_correct = $option['is_correct'];
                                $exerciseOption->save();
                            } else {
                                // Nếu không tìm thấy, tạo mới
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
            'data' => $updatedData // Trả về dữ liệu đã cập nhật
        ], 200);
    }
    

    public function destroy(string $id)
    {
        // Tìm bài học theo ID
        $lesson = Lesson::find($id);

        // Kiểm tra xem bài học có tồn tại không
        if (!$lesson) {
            return response()->json(['message' => 'Lesson not found'], 404);
        }

        // Lấy loại bài học
        $lessonType = $lesson->type;

        // Xóa dữ liệu dựa trên loại mà không xóa hàng trong bảng lessons
        switch ($lessonType) {
            case 0: // Video
                $lessonVideo = LessonVideo::where('lesson_id', $lesson->id)->first();
                if ($lessonVideo) {
                    $lessonVideo->delete(); // Xóa video
                }
                break;

            case 1: // Text
                $lessonText = LessonText::where('lesson_id', $lesson->id)->first();
                if ($lessonText) {
                    $lessonText->delete(); // Xóa văn bản
                }
                break;

            case 2: // Exercise
                $lessonExercise = LessonExercise::where('lesson_id', $lesson->id)->first();
                if ($lessonExercise) {
                    // Xóa các tùy chọn của bài tập
                    ExerciseOption::where('lesson_exercise_id', $lessonExercise->id)->delete();
                    $lessonExercise->delete(); // Xóa bài tập
                }
                break;

            default:
                return response()->json(['message' => 'Invalid lesson type'], 400);
        }

        // Không xóa bài học trong bảng lessons
        return response()->json(['message' => 'Associated data deleted successfully'], 200);
    }

}
