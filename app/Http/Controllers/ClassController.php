<?php

namespace App\Http\Controllers;

use App\Models\Classes;
use App\Models\Comment;
use App\Models\Progress;
use App\Models\Subscribe;
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
            // Đếm số lượng người đăng ký cho lớp học
            $subscribeCount = Subscribe::where('class_id', $class->id)->count();

            // Gộp dữ liệu vào InfoData
            $infoData = [
                'info' => [
                    [
                        'user' => $userInfo,
                        'comment_count' => $commentCount,
                        'subscribe_count' => $subscribeCount,
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
        $input_courses = $request->input("courses");
        $input_lessons = $request->input("lessons");
        $input_contents = $request->input("contents");

        $classID = DB::table("classes")->insertGetId([
            'user_id' => $input_cart['user_id'],
            'thumbnail' => $input_cart['thumbnail'],
            'description' => $input_cart['description'],
            'password' => $input_cart['password'],
            'type' => $input_cart['type'],
            'name' => $input_cart['name'],
        ]);
        foreach ($input_courses as $course) {
            $course_id = $course['id'];
            $lessonArr = array_filter($input_lessons, function ($item) use ($course_id) {
                return $item['course_id'] === $course_id;
            });
            $courseID = DB::table('course')->insertGetId([
                'class_id' => $classID,
                'name' => $course['name'],
            ]);
            foreach ($lessonArr as $lesson) {
                $lesson_id = $lesson['id'];
                $contentArr = array_filter($input_contents, function ($item) use ($lesson_id) {
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

        // Lấy thông tin người dùng từ user_id
        $user = User::find($class->user_id);
        $userInfo = [
            'user_id' => $user->id,
            'name' => $user->name,
            'avatar' => $user->avatar,
        ];

        // Đếm số lượng comment cho lớp học
        $commentCount = Comment::where('class_id', $class->id)->count();
        // Đếm số lượng người đăng ký cho lớp học
        $subscribeCount = Subscribe::where('class_id', $class->id)->count();

        // Gộp dữ liệu vào infoData
        $infoData = [
            'info' => [
                [
                    'user' => $userInfo,
                    'comment_count' => $commentCount,
                    'subscribe_count' => $subscribeCount,
                ],
            ],
        ];

        // Trả về đối tượng dưới dạng JSON
        return response()->json([
            'message' => 'success',
            'data' => array_merge(['class' => $class], $infoData) // Trả về đối tượng lớp học và thông tin người dùng
        ], 200);
    }
    public function ownShow(string $user_id)
    {
        // Lấy tất cả lớp học theo user_id
        $classes = Classes::where('user_id', $user_id)->get();
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
            // Đếm số lượng người đăng ký cho lớp học
            $subscribeCount = Subscribe::where('class_id', $class->id)->count();

            // Gộp dữ liệu vào InfoData
            $infoData = [
                'info' => [
                    [
                        'user' => $userInfo,
                        'comment_count' => $commentCount,
                        'subscribe_count' => $subscribeCount,
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
    public function ownStudent(string $class_id)
    {
        // Lấy lớp học theo class_id
        $class = Classes::findOrFail($class_id);
        $course_id = $class->course_id; // Lấy course_id từ lớp học

        // Lấy tất cả sinh viên đã đăng ký vào lớp học theo class_id
        $subscribers = Subscribe::where('class_id', $class_id)->get();
        $studentsData = [];

        // Lặp qua từng subscriber để lấy thông tin người dùng và số bài học đã học
        foreach ($subscribers as $subscriber) {
            $user = User::find($subscriber->user_id);

            if ($user) { // Kiểm tra xem người dùng có tồn tại không
                // Đếm số bài học đã hoàn thành
                $completedLessonsCount = Progress::where('user_id', $user->id)
                    ->where('course_id', $course_id)
                    ->count();

                $studentsData[] = [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'avatar' => $user->avatar,
                    'email' => $user->email,
                    'progress' => $completedLessonsCount,
                ];
            }
        }

        return response()->json([
            'message' => 'success',
            'data' => $studentsData,
        ], 200);
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
            'password' => $input_cart['password'],
            'type' => $input_cart['type'],
            'name' => $input_cart['name'],
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
            $lessonArr = array_filter($input_lessons, function ($item) use ($courseID) {
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
                $contentArr = array_filter($input_contents, function ($item) use ($lessonID) {
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
        DB::table('lesson_text')->whereIn('lesson_id', function ($query) use ($classId) {
            $query->select('id')->from('lesson')->whereIn('course_id', function ($subQuery) use ($classId) {
                $subQuery->select('id')->from('course')->where('class_id', $classId);
            });
        })->delete();

        DB::table('lesson_video')->whereIn('lesson_id', function ($query) use ($classId) {
            $query->select('id')->from('lesson')->whereIn('course_id', function ($subQuery) use ($classId) {
                $subQuery->select('id')->from('course')->where('class_id', $classId);
            });
        })->delete();

        DB::table('lesson_exercise')->whereIn('lesson_id', function ($query) use ($classId) {
            $query->select('id')->from('lesson')->whereIn('course_id', function ($subQuery) use ($classId) {
                $subQuery->select('id')->from('course')->where('class_id', $classId);
            });
        })->delete();

        DB::table('exercise_options')->whereIn('lesson_exercise_id', function ($query) use ($classId) {
            $query->select('id')->from('lesson_exercise')->whereIn('lesson_id', function ($subQuery) use ($classId) {
                $subQuery->select('id')->from('lesson')->whereIn('course_id', function ($subSubQuery) use ($classId) {
                    $subSubQuery->select('id')->from('course')->where('class_id', $classId);
                });
            });
        })->delete();

        // Xóa lesson và course
        DB::table('lesson')->whereIn('course_id', function ($query) use ($classId) {
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

    public function privateValidate(Request $req, string $class_id)
    {
        // Tìm lớp dựa trên class_id
        $class = Classes::find($class_id);

        // Nếu lớp không tồn tại, trả về lỗi
        if (!$class) {
            return response()->json([
                'message' => 'Class not found.',
            ], 404); // Không tìm thấy lớp
        }

        // So sánh mật khẩu từ yêu cầu với mật khẩu của lớp
        if ($class->password && $req->password === $class->password) {
            // Nếu mật khẩu đúng, trả về dữ liệu lớp
            return response()->json([
                'message' => 'Password is correct.',
                'data' => $class, // Trả về thông tin lớp
            ], 200); // Mật khẩu đúng
        } else {
            return response()->json([
                'message' => 'Invalid password.',
            ], 403); // Mật khẩu sai
        }
    }
    public function filterData(Request $request)
    {
        // Lấy các tham số từ query param
        $class = $request->query('class', 'all'); // Mặc định là 'all' nếu không có giá trị
        $type = $request->query('type', 'all');
        $filter = $request->query('filter', 'all');

        $query = Classes::query();

        // Lọc theo loại lớp (private/public)
        if ($class == 'private') {
            $query->where('type', 'private');
        } else if ($class == 'public') {
            $query->where('type', 'public');
        }

        // Lọc theo subject
        if ($type != "all") {
            $query->where('subject', $type);
        }

        // Đếm số người đăng ký
        $query->withCount('subscribes'); // Đếm số người đăng ký ngay từ đầu

        // Sắp xếp theo điều kiện filter
        if ($filter == 'newest') {
            $query->orderBy('updated_at', 'desc'); // Sắp xếp từ mới đến cũ
        } else if ($filter == 'lastest') {
            $query->orderBy('updated_at', 'asc'); // Sắp xếp từ cũ đến mới
        } elseif ($filter == 'population') {
            $query->orderBy('subscribes_count', 'desc'); // Sắp xếp theo số người đăng ký
        }

        // Lấy kết quả từ cơ sở dữ liệu
        $classes = $query->with('user')->get();

        // Kiểm tra nếu không có dữ liệu
        if ($classes->isEmpty()) {
            return response()->json(['message' => 'No classes found.'], 404);
        }

        // Tạo cấu trúc dữ liệu theo định dạng bạn cần
        $result = [];
        foreach ($classes as $class) {
            $result[] = [
                'class' => [
                    'id' => $class->id,
                    'name' => $class->name,
                    'user_id' => $class->user_id,
                    'description' => $class->description,
                    'thumbnail' => $class->thumbnail,
                    'deleted' => $class->deleted,
                    'created_at' => $class->created_at,
                    'updated_at' => $class->updated_at,
                    'password' => $class->password,
                    'type' => $class->type,
                    'subject' => $class->subject
                ],
                'info' =>[
                    [
                        'user' => [
                            'user_id' => $class->user->id,
                            'name' => $class->user->name,
                            'avatar' => $class->user->avatar,
                        ],
                        'comment_count' => 0, // Hoặc lấy dữ liệu từ bảng comment nếu có
                        'subscribe_count' => $class->subscribes_count // Lấy số lượng đăng ký nếu có
                    ]
                ]
            ];
        }

        // Trả về kết quả (JSON)
        return response()->json($result);
    }
    public function topRate()
    {
        // Lấy 4 lớp có số lượng đăng ký (subscribes) nhiều nhất cho cả private và public
        $classes = Classes::whereIn('type', ['private', 'public'])
            ->withCount('subscribes')
            ->orderBy('subscribes_count', 'desc')
            ->get()
            ->groupBy('type');

        // Helper nội bộ để format dữ liệu lớp
        $formatClasses = function ($classes) {
            $result = [];
            foreach ($classes as $class) {
                $result[] = [
                    'class' => [
                        'id' => $class->id,
                        'name' => $class->name,
                        'user_id' => $class->user_id,
                        'description' => $class->description,
                        'thumbnail' => $class->thumbnail,
                        'deleted' => $class->deleted,
                        'created_at' => $class->created_at,
                        'updated_at' => $class->updated_at,
                        'password' => $class->password,
                        'type' => $class->type,
                        'subject' => $class->subject
                    ],
                    'info' => [
                        [
                            'user' => [
                                'user_id' => $class->user->id,
                                'name' => $class->user->name,
                                'avatar' => $class->user->avatar,
                            ],
                            'comment_count' => 0, // Hoặc lấy dữ liệu từ bảng comment nếu có
                            'subscribe_count' => $class->subscribes_count // Lấy số lượng đăng ký nếu có
                        ],
                        // Bạn có thể thêm nhiều thông tin khác ở đây
                    ]
                ];
                
            }
            return $result;
        };

        // Lấy ra tối đa 4 lớp từ nhóm private
        if (isset($classes['private'])) {
            $result['private'] = $formatClasses($classes['private']->take(4));
        }

        // Lấy ra tối đa 4 lớp từ nhóm public
        if (isset($classes['public'])) {
            $result['public'] = $formatClasses($classes['public']->take(4));
        }

        // Trả về kết quả dưới dạng JSON
        return response()->json($result);
    }




}
