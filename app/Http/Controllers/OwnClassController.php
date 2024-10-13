<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Progress;
use App\Models\Subscribe;
use App\Models\User;
use Illuminate\Http\Request;

class OwnClassController extends Controller
{
    public function show(string $user_id) {
        $classes = Subscribe::join('classes', 'subscribe.class_id', '=', 'classes.id')
            ->where('subscribe.user_id', $user_id)
            ->select('classes.*')
            ->get();
    
        $infoDataArray = [];
    
        foreach ($classes as $class) {
            // Lấy thông tin người dùng từ user_id
            $user = User::find($class->user_id);
            if (!$user) {
                continue;
            }
    
            $userInfo = [
                'user_id' => $user->id,
                'name' => $user->name,
                'avatar' => $user->avatar,
            ];
    
            // Đếm số lượng comment cho lớp học
            $commentCount = Comment::where('class_id', $class->id)->count();
            // Đếm số lượng người đăng ký cho lớp học
            $subscribeCount = Subscribe::where('class_id', $class->id)->count();
    
            // Lấy tất cả các course cho class
            $courses = Course::where('class_id', $class->id)->get();
            $totalLessons = 0;
            $currentLesson = null;
    
            foreach ($courses as $course) {
                // Lấy tất cả các bài học cho course
                $lessons = Lesson::where('course_id', $course->id)->get();
                $totalLessons += $lessons->count();
    
                // Kiểm tra tiến trình của người dùng cho các bài học trong course
                $progressData = $this->getProgressData($user_id, $lessons);
                
                // Lưu số thứ tự bài học hiện tại
                if ($progressData['completed_count']) {
                    $currentLesson = $progressData['completed_count'];
                }
            }
    
            // Gộp dữ liệu vào infoData
            $infoData = [
                'info' => [
                    'user' => $userInfo,
                    'comment_count' => $commentCount,
                    'subscribe_count' => $subscribeCount,
                ],
                'progress' => [
                    'current_lesson' => $currentLesson, // Trả về số thứ tự bài học chưa hoàn thành
                    'total_lesson' => (string)$totalLessons, // Chuyển đổi tổng số bài học sang chuỗi
                ]
            ];
            $infoDataArray[] = array_merge(['class' => $class], $infoData);
        }
    
        return response()->json($infoDataArray);
    }
    
    
    private function getProgressData(string $user_id, $lessons) {
        // Đếm số lượng bài học đã hoàn thành
        $completedLessonsCount = Progress::where('user_id', $user_id)
            ->whereIn('lesson_id', $lessons->pluck('id'))
            ->where('is_completed', true)
            ->count();
    
        $totalLessonsCount = count($lessons); // Tổng số bài học
    
        return [
            'completed_count' => $completedLessonsCount, // Số lượng bài học đã hoàn thành
            'total_lesson' => $totalLessonsCount, // Tổng số bài học
        ];
    }
    
}
