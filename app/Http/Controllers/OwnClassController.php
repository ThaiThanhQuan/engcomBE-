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
    public function show(string $user_id)
    {
        $classes = Subscribe::join('classes', 'subscribe.class_id', '=', 'classes.id')
            ->where('subscribe.user_id', $user_id)
            ->where('classes.deleted', 1)
            ->select('classes.*')
            ->get();
        $infoDataArray = [];
        foreach ($classes as $class) {
            $user = User::find($class->user_id);
            if (!$user) {
                continue;
            }
            $userInfo = [
                'user_id' => $user->id,
                'name' => $user->name,
                'avatar' => $user->avatar,
            ];
            $commentCount = Comment::where('class_id', $class->id)->count();
            $subscribeCount = Subscribe::where('class_id', $class->id)->count();
            $courses = Course::where('class_id', $class->id)->get();
            $totalLessons = 0;
            $currentLesson = 0;
            foreach ($courses as $course) {
                $lessons = Lesson::where('course_id', $course->id)->get();
                $totalLessons += $lessons->count();
                $progressData = $this->getProgressData($user_id, $lessons);
                $currentLesson += $progressData['completed_count'];
            }
            $infoData = [
                'info' => [
                    'user' => $userInfo,
                    'comment_count' => $commentCount,
                    'subscribe_count' => $subscribeCount,
                ],
                'progress' => [
                    'current_lesson' => $currentLesson,
                    'total_lesson' => (string) $totalLessons,
                ]
            ];
            $infoDataArray[] = array_merge(['class' => $class], $infoData);
        }
        return response()->json($infoDataArray);
    }

    private function getProgressData(string $user_id, $lessons)
    {
        $completedLessonsCount = Progress::where('user_id', $user_id)
            ->whereIn('lesson_id', $lessons->pluck('id'))
            ->where('is_completed', true)
            ->count();
        $totalLessonsCount = count($lessons);
        return [
            'completed_count' => $completedLessonsCount,
            'total_lesson' => $totalLessonsCount,
        ];
    }
}
