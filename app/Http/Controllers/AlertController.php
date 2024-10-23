<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use Illuminate\Http\Request;

class AlertController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Lấy tất cả các bản ghi từ bảng alerts
        $alerts = Alert::all();

        return response()->json([
            'status' => true,
            'data' => $alerts
        ]);
    }


    public function store(Request $request)
    {
        // Xác thực file upload
        $request->validate([
            'thumbnail' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Tối đa 2MB
        ]);

        // Khởi tạo mảng dữ liệu đầu vào
        $input = $request->all();

        // Kiểm tra xem có file upload không
        if ($request->hasFile('thumbnail')) {
            // Lấy file
            $file = $request->file('thumbnail');

            // Tạo tên file duy nhất
            $fileName = time() . '_' . $file->getClientOriginalName();

            // Lưu file vào storage
            $path = $file->storeAs('uploads', $fileName, 'public');

            // Thêm tên file vào input để lưu vào cơ sở dữ liệu
            $input['thumbnail'] = $fileName; // Lưu tên file vào trường 'thumbnail'
        }

        // Tạo bản ghi mới trong bảng alerts
        $alert = Alert::create($input);

        return response()->json([
            'status' => true,
            'message' => "Thêm thành công",
            'data' => $alert
        ]);
    }

    public function show(string $id)
    {

    }

    public function destroy(string $alertid)
    {
        // Tìm bản ghi theo ID
        $alert = Alert::find($alertid);
        $alert->delete();
        return response()->json([
            'status' => true,
            'message' => "Bản ghi đã được xóa thành công."
        ]);
    }

}
