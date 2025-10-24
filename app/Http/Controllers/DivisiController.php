<?php

namespace App\Http\Controllers;

use App\Models\Divisi;
use Illuminate\Http\Request;

class DivisiController extends Controller
{

    public function index()
    {
        $data = Divisi::orderBy('name','ASC')->get();

        return response()->json(['status'=>'success','data'=>$data],200);
    }
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:divisi',
        ]);

        Divisi::create([
            'name'=>$request->name,
            'email'=>$request->email,
        ]);
        
        return response()->json(['status'=>'success'],200);
    }

    public function update($id,Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required'
        ]);

        $divisi = Divisi::find($id);

        if (!$divisi){
            return response()->json(['error' => 'Data tidak ditemukan'], 401);
        }

        $divisi->update([
            'name'=>$request->name,
            'email'=>$request->email
        ]);

        return response()->json(['status'=>'success'],200);
    }
}
