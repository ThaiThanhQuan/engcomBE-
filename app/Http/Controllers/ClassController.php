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
        $classes = Classes::all();
        $costClasses = [];
        $privateClasses = [];
        $publicClasses = [];
        foreach ($classes as $class) {
            $user = User::find($class->user_id);
            $userInfo = [
                'user_id' => $user->id,
                'name' => $user->name,
                'avatar' => $user->avatar,
            ];
            $commentCount = Comment::where('class_id', $class->id)->count();
            $subscribeCount = Subscribe::where('class_id', $class->id)->count();
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
            'subject' => $input_cart['subject'],
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
                    if (isset($contents['questions']) && !empty($contents['questions'])) {
                        $exerciseID = DB::table('lesson_exercise')->insertGetId([
                            'lesson_id' => $lessonID,
                            'title' => $contents['title'],
                            'text' => $contents['text'],
                        ]);
                        foreach ($contents['questions'] as $item) {
                            DB::table('exercise_options')->insert([
                                'lesson_exercise_id' => $exerciseID,
                                'text' => $item['text'],
                                'is_correct' => $item['is_correct']
                            ]);
                        }
                    } else if (isset($contents['title']) && !empty($contents['title'])) {
                        DB::table('lesson_video')->insert([
                            'text' => $contents['text'],
                            'title' => $contents['title'],
                            'lesson_id' => $lessonID,
                        ]);
                    } else {
                        DB::table('lesson_text')->insert([
                            'text' => $contents['text'],
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
        $class = Classes::findOrFail($id);
        if ($class->deleted === 0 && $class->deleted !== null) {
            return response()->json([
                'message' => 'Class is deleted and cannot be accessed.',
            ], 403);
        }
        $user = User::find($class->user_id);
        $userInfo = [
            'user_id' => $user->id,
            'name' => $user->name,
            'avatar' => $user->avatar,
        ];
        $commentCount = Comment::where('class_id', $class->id)->count();
        $subscribeCount = Subscribe::where('class_id', $class->id)->count();
        $infoData = [
            'info' => [
                [
                    'user' => $userInfo,
                    'comment_count' => $commentCount,
                    'subscribe_count' => $subscribeCount,
                ],
            ],
        ];
        return response()->json([
            'message' => 'success',
            'data' => array_merge(['class' => $class], $infoData)
        ], 200);
    }

    public function ownShow(string $user_id)
    {
        $classes = Classes::where('user_id', $user_id)
            ->where(function ($query) {
                $query->where('deleted', NULL)
                    ->orWhere('deleted', 1);
            })
            ->get();
        $classData = [];
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
            $infoData = [
                'info' => [
                    [
                        'user' => $userInfo,
                        'comment_count' => $commentCount,
                        'subscribe_count' => $subscribeCount,
                    ],
                ],
            ];
            $classData[] = array_merge(['class' => $class], $infoData);
        }
        return response()->json([
            'data' => $classData,
            'message' => 'success'
        ]);
    }

    public function ownStudent(string $class_id)
    {
        $class = Classes::findOrFail($class_id);
        $course_id = $class->course_id;
        $subscribers = Subscribe::where('class_id', $class_id)->get();
        $studentsData = [];
        foreach ($subscribers as $subscriber) {
            $user = User::find($subscriber->user_id);
            if ($user) {
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
        $input = $request->only([
            'user_id',
            'name',
            'description',
            'password',
            'thumbnail',
            'type',
            'subject',
        ]);
        $updated = DB::table("classes")->where('id', $classId)->update($input);
        if ($updated) {
            return response()->json([
                'message' => 'Update success',
                'status' => true,
                'data' => $input,
            ]);
        } else {
            return response()->json([
                'message' => 'No changes made or class not found.',
                'status' => false,
            ], 200);
        }
    }


    public function destroy($classId)
    {
        $class = Classes::where('id', $classId)->first();

        if ($class) {
            $class->deleted = 0;
            $class->save();
            return response()->json(['message' => 'class da duoc xoa']);
        }
        return response()->json(['message' => 'Class not found.'], 404);
    }


    public function moreShow($type)
    {
        $classes = Classes::where('type', $type)->get();
        $classData = [];
        foreach ($classes as $class) {
            if ($class->deleted == 0) {
                continue;
            }
            $user = User::find($class->user_id);
            $userInfo = [
                'user_id' => $user->id,
                'name' => $user->name,
                'avatar' => $user->avatar,
            ];
            $commentCount = Comment::where('class_id', $class->id)->count();
            $infoData = [
                'info' => [
                    [
                        'user' => $userInfo,
                        'comment_count' => $commentCount,
                    ],
                ],
            ];
            $classData[] = array_merge(['class' => $class], $infoData);
        }

        return response()->json([
            'data' => $classData,
            'message' => 'success'
        ]);
    }

    public function privateValidate(Request $req, string $class_id)
    {
        $class = Classes::find($class_id);
        if (!$class) {
            return response()->json([
                'message' => 'Class not found.',
            ], 404);
        }
        if ($class->password && $req->password === $class->password) {
            return response()->json([
                'message' => 'Password is correct.',
                'data' => $class,
            ], 200);
        } else {
            return response()->json([
                'message' => 'Invalid password.',
            ], 403);
        }
    }
    public function filterData(Request $request)
    {
        $class = $request->query('class', 'all');
        $type = $request->query('type', 'all');
        $filter = $request->query('filter', 'all');
        $query = Classes::query();
        $query->where('deleted', 1);
        if ($class == 'private') {
            $query->where('type', 'private');
        } else if ($class == 'public') {
            $query->where('type', 'public');
        }
        if ($type != "all") {
            $query->where('subject', $type);
        }
        $query->withCount(['subscribes', 'comments']);
        if ($filter == 'newest') {
            $query->orderBy('updated_at', 'desc');
        } else if ($filter == 'lastest') {
            $query->orderBy('updated_at', 'asc');
        } elseif ($filter == 'population') {
            $query->orderBy('subscribes_count', 'desc');
        }
        $classes = $query->with('user')->get();
        if ($classes->isEmpty()) {
            return response()->json(['message' => 'No classes found.'], 404);
        }
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
                        'comment_count' => $class->comments_count,
                        'subscribe_count' => $class->subscribes_count
                    ]
                ]
            ];
        }
        return response()->json($result);
    }

    public function topRate()
    {
        $classes = Classes::where('deleted', 1)
            ->whereIn('type', ['private', 'public'])
            ->select('classes.*')
            ->leftJoin('subscribe', 'classes.id', '=', 'subscribe.class_id')
            ->leftJoin('comments', 'classes.id', '=', 'comments.class_id')
            ->groupBy('classes.id')
            ->orderByRaw('COUNT(subscribe.id) DESC')
            ->get();
        $result = [];
        foreach ($classes as $class) {
            $subscribeCount = Subscribe::where('class_id', $class->id)->count();
            $commentCount = Comment::where('class_id', $class->id)->count();
            $classComments = Comment::where('class_id', $class->id)->get();
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
                        'comment_count' => $commentCount,
                        'subscribe_count' => $subscribeCount
                    ],
                ],
                'comments' => $classComments
            ];
        }
        $groupedResult = [
            'private' => [],
            'public' => []
        ];
        foreach ($result as $item) {
            $groupedResult[$item['class']['type']][] = $item;
        }
        return response()->json($groupedResult);
    }


}
