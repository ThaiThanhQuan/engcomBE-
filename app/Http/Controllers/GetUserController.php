<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;

class GetUserController extends Controller
{
   
    public function index()
    {
        $users = DB::table('users')
            ->where('deleted', 1)
            ->select('id', 'name as fullname', 'phone_number', 'address', 'created_at', 'updated_at', 'role_id', 'avatar')
            ->get();
        
    
        return response()->json([
            'data' => $users
        ]);
    }

   
   
    public function store(Request $request)
    {
        //
    }

    
    public function show()
    {
        
    }
    

 
    public function update(Request $request, string $id)
    {
        //
    }

    
    public function destroy(string $id)
    {
        
    }
}
