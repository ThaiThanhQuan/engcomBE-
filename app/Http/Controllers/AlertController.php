<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use Illuminate\Http\Request;

class AlertController extends Controller
{
    public function index()
    {
        $alerts = Alert::orderBy('created_at', 'desc')->get();

        return response()->json([
            'status' => true,
            'data' => $alerts
        ]);
    }

    public function store(Request $request)
    {

        $input = $request->all();
        if ($request->hasFile('thumbnail')) {
            $file = $request->file('thumbnail');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('uploads', $fileName, 'public');
            $input['thumbnail'] = $fileName;
        }
        $alert = Alert::create($input);
        return response()->json([
            'status' => true,
            'message' => "Thêm thành công",
            'data' => $alert
        ]);
    }

    public function destroy(string $alertid)
    {
        $alert = Alert::find($alertid);
        $alert->delete();
        return response()->json([
            'status' => true,
            'message' => "Bản ghi đã được xóa thành công."
        ]);
    }

}
