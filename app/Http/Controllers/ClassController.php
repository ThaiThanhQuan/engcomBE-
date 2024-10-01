<?php

namespace App\Http\Controllers;

use App\Models\Classes;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Http\Request;
use DB;
class ClassController extends Controller
{
    public function index()
    {
        // Lấy tất cả lớp học
        $classes = Classes::all();

        // Khởi tạo mảng để lưu dữ liệu theo từng loại
        $costClasses = [];
        $privateClasses = [];
        $publicClasses = [];

        // Phân loại các lớp học theo type
        foreach ($classes as $class) {
            // Lấy thông tin người dùng từ user_id
            $user = User::find($class->user_id);
            $userInfo = [
                'user_id' => $user->id,
                'name' => $user->name,
                'avatar' => $user->avatar,
            ];

            // Đếm số lượng comment cho lớp học
            $commentCount = Comment::where('class_id', $class->id)->count();

            // Gộp dữ liệu vào InfoData
            $infoData = [
                'info' => [
                    [
                        'user' => $userInfo,
                        'comment_count' => $commentCount,
                    ],
                ],
            ];

            switch ($class->type) {
                case 'cost':
                    $costClasses[] = array_merge(['class' => $class], $infoData);
                    break;
                case 'private':
                    $privateClasses[] = array_merge(['class' => $class], $infoData);
                    break;
                case 'public':
                    $publicClasses[] = array_merge(['class' => $class], $infoData);
                    break;
            }
        }

        return response()->json([
            'data' => [
                'cost' => $costClasses,
                'private' => $privateClasses,
                'public' => $publicClasses,
            ],
            'message' => 'success'
        ]);
    }
    public function store(Request $request)
    {
        $input_cart = $request->input("carts");
        $input_courses = $request-> input("courses");
        $input_lessons = $request->  input("lessons");
        $input_contents = $request-> input("contents");

        $classID = DB::table("classes")->insertGetId([
            'user_id' => $input_cart['user_id'],
            'thumbnail' => $input_cart['thumbnail'],
            'description' => $input_cart['description'],
            'discount' => $input_cart['discount'],
            'price' => $input_cart['price'],
            'password' => $input_cart['password'],
            'type' => $input_cart['type'],
            'name' => $input_cart['name'],
            'total' => $input_cart['total'],
        ]);
        foreach ($input_courses as $course) {
            $course_id = $course['id'];
            $lessonArr = array_filter($input_lessons, function($item) use ($course_id) {
                return $item['course_id'] === $course_id;
            });
            $courseID = DB::table('course')->insertGetId([
                'class_id' => $classID,
                'name' => $course['name'],
            ]);
            foreach ($lessonArr as $lesson ) {
                $lesson_id = $lesson['id'];
                $contentArr = array_filter($input_contents, function($item) use ($lesson_id) {
                    return $item['lesson_id'] === $lesson_id;
                });
                $lessonID = DB::table('lesson')->insertGetId([
                    'course_id' => $courseID,
                    'name' => $lesson['name'],
                    'type' => $lesson['type']
                ]);
                foreach ($contentArr as $contents) {
                    // Kiểm tra nếu khóa 'questions' tồn tại và không rỗng
                    if (isset($contents['questions']) && !empty($contents['questions'])) {
                        $exerciseID = DB::table('lesson_exercise')->insertGetId([
                            'lesson_id' => $lessonID,
                            'title' => $contents['title'],
                            'content' => $contents['content'],
                        ]);
                        foreach ($contents['questions'] as $item) {
                            DB::table('exercise_options')->insertGetId([
                                'lesson_exercise_id' => $exerciseID,
                                'text' => $item['name'],
                                'is_correct' => $item['is_correct']
                            ]);
                        }
                    } 
                    // Kiểm tra nếu khóa 'video' tồn tại và không rỗng
                    else if (isset($contents['video']) && !empty($contents['video'])) {
                        DB::table('lesson_video')->insert([
                            'content' => $contents['content'],
                            'video' => $contents['video'],
                            'lesson_id' => $lessonID,
                        ]);
                    } 
                    // Xử lý phần còn lại
                    else {
                        DB::table('lesson_text')->insert([
                            'content' => $contents['content'],
                            'lesson_id' => $lessonID,
                        ]);
                    }
                }        
            }

        }
        return response()->json([
            'message' => 'success',
            'status' => true,
            'data' => [
                'carts' => $input_cart,
                'courses' => $input_courses,
                'lessons' => $input_lessons,
                'content' => $input_contents,
            ]
        ]);
    }
    public function show(string $id)
    {
        // Tìm đối tượng theo ID
        $class = Classes::findOrFail($id);
    
        // Trả về đối tượng dưới dạng JSON
        return response()->json([
            'message' => 'success',
            'data' => $class // Trả về đối tượng
        ], 200);
    }
    public function ownShow(string $user_id)
    {
        $class = Classes::where('user_id', $user_id)->get();

        return response()->json([
            'message' => 'success',
            'data' => $class
        ],200);
    }

    public function update(Request $request, $classId)
    {
        $input_cart = $request->input("carts");
        $input_courses = $request->input("courses");
        $input_lessons = $request->input("lessons");
        $input_contents = $request->input("contents");

        // Cập nhật thông tin class
        DB::table("classes")->where('id', $classId)->update([
            'user_id' => $input_cart['user_id'],
            'thumbnail' => $input_cart['thumbnail'],
            'description' => $input_cart['description'],
            'discount' => $input_cart['discount'],
            'price' => $input_cart['price'],
            'password' => $input_cart['password'],
            'type' => $input_cart['type'],
            'name' => $input_cart['name'],
            'total' => $input_cart['total'],
        ]);

        // Cập nhật hoặc thêm mới courses
        foreach ($input_courses as $course) {
                $courseID = $course['id'];
                // Kiểm tra xem course có tồn tại không
                $existingCourse = DB::table('course')->where('id', $courseID)->first();
                if ($existingCourse) {
                    // Cập nhật nếu tồn tại
                    DB::table('course')->where('id', $courseID)->update([
                        'class_id' => $classId,
                        'name' => $course['name'],
                    ]);
                } else {
                    // Nếu không tìm thấy, tạo mới
                    $courseID = DB::table('course')->insertGetId([
                        'class_id' => $classId,
                        'name' => $course['name'],
                    ]);
                }
            // Cập nhật hoặc thêm mới lessons
            $lessonArr = array_filter($input_lessons, function($item) use ($courseID) {
                return $item['course_id'] === $courseID;
            });
            
            foreach ($lessonArr as $lesson) {
                    $lessonID = $lesson['id'];
                    // Kiểm tra xem lesson có tồn tại không
                    $existingLesson = DB::table('lesson')->where('id', $lessonID)->first();
                    if ($existingLesson) {
                        // Cập nhật lesson nếu tồn tại
                        DB::table('lesson')->where('id', $lessonID)->update([
                            'course_id' => $courseID,
                            'name' => $lesson['name'],
                            'type' => $lesson['type']
                        ]);
                    } else {
                        // Nếu không tìm thấy, tạo mới
                        $lessonID = DB::table('lesson')->insertGetId([
                            'course_id' => $courseID,
                            'name' => $lesson['title'],
                            'type' => $lesson['type']
                        ]);
                    }
                // Cập nhật hoặc thêm mới content
                $contentArr = array_filter($input_contents, function($item) use ($lessonID) {
                    return $item['lesson_id'] === $lessonID;
                });
                foreach ($contentArr as $contents) {
                    $contentID = $contents['id'] ?? null;
                
                    if ($contentID) {
                        // Cập nhật nội dung nếu tồn tại
                        $existingContent = DB::table('lesson_exercise')->where('id', $contentID)->first();
                        
                        if ($existingContent) {
                            // Cập nhật nội dung
                            DB::table('lesson_exercise')->where('id', $contentID)->update([
                                'lesson_id' => $lessonID,
                                'title' => $contents['title'] ?? null,
                                'content' => $contents['content'] ?? null,
                            ]);
                
                            // Cập nhật câu hỏi nếu có
                            if (isset($contents['questions']) && !empty($contents['questions'])) {
                                foreach ($contents['questions'] as $item) {
                                    DB::table('exercise_options')->updateOrInsert(
                                        ['lesson_exercise_id' => $contentID, 'text' => $item['name']],
                                        ['is_correct' => $item['is_correct']]
                                    );
                                }
                            }
                        }
                    } else {
                        // Nếu không có ID, kiểm tra các trường hợp khác
                        if (isset($contents['questions']) && !empty($contents['questions'])) {
                            // Tạo mới cho lesson_exercise
                            $exerciseID = DB::table('lesson_exercise')->insertGetId([
                                'lesson_id' => $lessonID,
                                'title' => $contents['title'] ?? null,
                                'content' => $contents['content'] ?? null,
                            ]);
                
                            foreach ($contents['questions'] as $item) {
                                DB::table('exercise_options')->insert([
                                    'lesson_exercise_id' => $exerciseID,
                                    'text' => $item['name'],
                                    'is_correct' => $item['is_correct']
                                ]);
                            }
                        } else if (isset($contents['video']) && !empty($contents['video'])) {
                            // Tạo mới cho lesson_video
                            DB::table('lesson_video')->insert([
                                'content' => $contents['content'] ?? null,
                                'video' => $contents['video'],
                                'lesson_id' => $lessonID,
                            ]);
                        } else if (isset($contents['text'])) {
                            // Tạo mới cho lesson_text
                            DB::table('lesson_text')->insert([
                                'content' => $contents['text'],
                                'lesson_id' => $lessonID,
                            ]);
                        }
                    }
                }
                               
            }
        }

        return response()->json([
            'message' => 'Update success',
            'status' => true,
            'data' => [
                'carts' => $input_cart,
                'courses' => $input_courses,
                'lessons' => $input_lessons,
                'content' => $input_contents,
            ]
        ]);
    }

    public function destroy($classId)
    {
        // Xóa tất cả nội dung liên quan đến lớp học
        DB::table('lesson_text')->whereIn('lesson_id', function($query) use ($classId) {
            $query->select('id')->from('lesson')->whereIn('course_id', function($subQuery) use ($classId) {
                $subQuery->select('id')->from('course')->where('class_id', $classId);
            });
        })->delete();

        DB::table('lesson_video')->whereIn('lesson_id', function($query) use ($classId) {
            $query->select('id')->from('lesson')->whereIn('course_id', function($subQuery) use ($classId) {
                $subQuery->select('id')->from('course')->where('class_id', $classId);
            });
        })->delete();

        DB::table('lesson_exercise')->whereIn('lesson_id', function($query) use ($classId) {
            $query->select('id')->from('lesson')->whereIn('course_id', function($subQuery) use ($classId) {
                $subQuery->select('id')->from('course')->where('class_id', $classId);
            });
        })->delete();

        DB::table('exercise_options')->whereIn('lesson_exercise_id', function($query) use ($classId) {
            $query->select('id')->from('lesson_exercise')->whereIn('lesson_id', function($subQuery) use ($classId) {
                $subQuery->select('id')->from('lesson')->whereIn('course_id', function($subSubQuery) use ($classId) {
                    $subSubQuery->select('id')->from('course')->where('class_id', $classId);
                });
            });
        })->delete();

        // Xóa lesson và course
        DB::table('lesson')->whereIn('course_id', function($query) use ($classId) {
            $query->select('id')->from('course')->where('class_id', $classId);
        })->delete();

        DB::table('course')->where('class_id', $classId)->delete();

        // Cuối cùng, xóa lớp học
        DB::table('classes')->where('id', $classId)->delete();

        return response()->json([
            'message' => 'Class deleted successfully',
            'status' => true,
        ]);
    }

    public function moreShow($type)
    {
        // Lấy tất cả lớp học theo loại
        $classes = Classes::where('type', $type)->get();

        // Khởi tạo mảng để lưu dữ liệu
        $classData = [];

        // Phân loại và lấy thông tin người dùng, số lượng comment
        foreach ($classes as $class) {
            // Lấy thông tin người dùng từ user_id
            $user = User::find($class->user_id);
            $userInfo = [
                'user_id' => $user->id,
                'name' => $user->name,
                'avatar' => $user->avatar,
            ];

            // Đếm số lượng comment cho lớp học
            $commentCount = Comment::where('class_id', $class->id)->count();

            // Gộp dữ liệu vào InfoData
            $infoData = [
                'info' => [
                    [
                        'user' => $userInfo,
                        'comment_count' => $commentCount,
                    ],
                ],
            ];

            // Thêm vào mảng dữ liệu với cấu trúc giống như yêu cầu trước
            $classData[] = array_merge(['class' => $class], $infoData);
        }

        return response()->json([
            'data' => $classData,
            'message' => 'success'
        ]);
    }

}
