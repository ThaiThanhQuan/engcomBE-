<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
class ClassController extends Controller
{
    public function index()
    {
        
    }

    public function store(Request $request)
    {
        $input_cart = $request->input("carts");
        $input_courses = $request-> input("courses");
        $input_lessons = $request->  input("lessons");
        $input_contents = $request-> input("contents");

        $class = DB::table("classes")->insertGetId([
            'thumbnail' => $input_cart['banner'],
            'desc' => $input_cart['desc'],
            'discount' => $input_cart['discount'],
            'id' => $input_cart['id'],
            'price' => $input_cart['price'],
            'title' => $input_cart['title'],
            'total' => $input_cart['total'],
            'type' => $input_cart['type']
        ]);
        foreach ($input_courses as $course) {
            DB::table('course')->insertGetId([
                'id' => $course[0]['id'],
                'title' => $course[0]['title'],
            ]);
        }
      
        foreach ($input_lessons as $lesson ) {
            DB::table('lesson')->insert([
                'course_id' => $lesson['course_id'],
                'id' => $lesson['id'],
                'title' => $lesson['title'],
                'type' => $lesson['type']
            ]);
        }
        foreach ($input_contents as $contents) {
            DB::table('content')->insert([
                'id' => $contents['id'],
                'lession_id' => $contents['lesson_id']
            ]);
        }
    }
    public function show(string $id)
    {
        
    }
    public function update(Request $request, string $id)
    {
        //
    }

    public function destroy(string $id)
    {
        //
    }
}
