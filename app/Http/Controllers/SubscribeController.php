<?php
namespace App\Http\Controllers;

use App\Models\Subscribe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubscribeController extends Controller
{
    public function index()
    {
        // Lấy danh sách tất cả các đăng ký
        $subscribes = Subscribe::all();
        return response()->json([
            'message' => 'Subscribes retrieved successfully.',
            'data' => $subscribes,
        ]);
    }

    public function store(Request $request)
    {
        // Tạo đăng ký mới mà không cần xác thực
        $subscribe = Subscribe::create($request->only('class_id', 'user_id'));
        return response()->json([
            'message' => 'Subscribe created successfully.',
            'data' => $subscribe,
        ], 201);
    }
    public function show(string $id)
    {
        $userSubscribes = DB::table('subscribe')
            ->join('classes', 'subscribe.class_id', '=', 'classes.id')
            ->select('subscribe.*', 'classes.*') // Lấy tất cả thông tin từ cả hai bảng
            ->where('subscribe.user_id', $id)
            ->get();

        return response()->json([
            'message' => 'subscribe for user retrieved successfully.',
            'data' => $userSubscribes,
        ]);
    }

    
    

    public function update(Request $request, Subscribe $subscribe)
    {
        // Cập nhật thông tin đăng ký mà không cần xác thực
        $subscribe->update($request->only('class_id', 'user_id'));
        return response()->json([
            'message' => 'Subscribe updated successfully.',
            'data' => $subscribe,
        ]);
    }
    public function destroy(Subscribe $subscribe)
    {
        $subscribeData = $subscribe; // Lưu thông tin để trả về
        $subscribe->delete();
        return response()->json([
            'message' => 'Subscribe deleted successfully.',
            'data' => $subscribeData,
        ], 200); // Trả về 200 và thông tin
    }
 
}
