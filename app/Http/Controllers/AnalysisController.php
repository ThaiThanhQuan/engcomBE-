<?php

namespace App\Http\Controllers;

use App\Models\Classes;
use App\Models\User;
use Illuminate\Http\Request;

class AnalysisController extends Controller
{
    public function total()
    {
        $totalClassesDeleted = Classes::where('deleted', 1)->count();
        $totalUsersDeleted = User::where('deleted', 1)->count();
        return response()->json([
            'classes_count' => $totalClassesDeleted,
            'users_count' => $totalUsersDeleted,
        ], 200);
    }

    public function separate()
    {
        $totalStudentsDeleted = User::where('deleted', 1)->where('role_id', 2)->count();
        $totalTeachersDeleted = User::where('deleted', 1)->where('role_id', 3)->count();
        return response()->json([
            'students_count' => $totalStudentsDeleted,
            'teacher_count' => $totalTeachersDeleted,
        ], 200);
    }

    public function statistics()
    {
        $today = now();
        $statistics = [];
        $totalDeletedUsers = User::where('deleted', 1)->count();
        $userDailyStats = [];
        $totalDeletedClasses = Classes::where('deleted', 1)->count();
        $classDailyStats = [];
        for ($i = 0; $i < 10; $i++) {
            $date = $today->copy()->subDays($i)->format('Y-m-d');
            $userCount = User::whereDate('created_at', $date)->where('deleted', 1)->count();
            $userDailyStats[] = [
                'date' => $date,
                'total' => $userCount,
            ];
            $classCount = Classes::whereDate('created_at', $date)->where('deleted', 1)->count();
            $classDailyStats[] = [
                'date' => $date,
                'total' => $classCount,
            ];
        }
        $statistics[] = [
            'title' => 'Users',
            'total' => $totalDeletedUsers,
            'daily' => $userDailyStats,
        ];
        $statistics[] = [
            'title' => 'Classes',
            'total' => $totalDeletedClasses,
            'daily' => $classDailyStats,
        ];
        foreach ($statistics as &$stat) {
            usort($stat['daily'], function ($a, $b) {
                return $a['date'] <=> $b['date'];
            });
        }
        return response()->json($statistics, 200);
    }
}
