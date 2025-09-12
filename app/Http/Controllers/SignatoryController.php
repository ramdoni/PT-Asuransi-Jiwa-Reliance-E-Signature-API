<?php

namespace App\Http\Controllers;

use App\Models\Signatory;
use Illuminate\Http\Request;

class SignatoryController extends Controller
{
    public function index()
    {
        $data = Signatory::orderBy('name','ASC')->get();

        return response()->json(['status'=>'success','data'=>$data],200);
    }
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'phone' => 'required',
            'position' => 'required',
            'email' => 'required|email|unique:signatories',
        ]);

        Signatory::create([
            'name'=>$request->name,
            'email'=>$request->email,
            'phone'=>$request->phone,
            'position'=>$request->position,
        ]);
        
        return response()->json(['status'=>'success'],200);
    }
    
}
