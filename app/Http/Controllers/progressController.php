<?php
namespace App\Http\Controllers;

use App\Models\Progress;
use Illuminate\Http\Request;

class ProgressController extends Controller
{
    public function store(Request $request, string $user_id)
{
    $progressArray = $request->all();

    // Kiểm tra xem user_id có tồn tại không
    if (!$user_id) {
        return response()->json(['error' => 'User ID is required'], 400);
    }

    foreach ($progressArray as $progressData) {
        $course_id = $progressData['course_id'];
        $lesson_id = $progressData['lesson_id'];
        $is_completed = $progressData['is_completed'];
        $is_in_progress = $progressData['is_in_progress'];
        // Kiểm tra nếu is_in_progress là null
        if (is_null($is_in_progress)) {
            return response()->json(['error' => 'is_in_progress is null'], 400);
        }

        // Tìm kiếm progress của user cho course và lesson
        $progress = Progress::where('user_id', $user_id)
            ->where('course_id', $course_id)
            ->where('lesson_id', $lesson_id)
            ->first();

        if ($progress) {
            // Cập nhật các thuộc tính cần thiết
            $progress->is_completed = $is_completed;
            $progress->is_in_progress = $is_in_progress;
            $progress->save();
        } else {
            // Nếu chưa tồn tại, tạo mới
            Progress::create([
                'user_id' => $user_id,
                'course_id' => $course_id,
                'lesson_id' => $lesson_id,
                'is_completed' => $is_completed,
                'is_in_progress' => $is_in_progress,
            ]);
        }
    }

    return response()->json(['success' => true]);
}

}


