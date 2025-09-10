<?php

namespace App\Http\Controllers;

use App\Models\JenisDokument;
use Illuminate\Http\Request;

class JenisDokumenController extends Controller
{

    public function index()
    {
        $data = JenisDokument::get();

        return response()->json(['status'=>'success','data'=>$data],200);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
        ]);

        JenisDokument::create([
            'name'=>$request->name,
        ]);
        
        return response()->json(['status'=>'success'],200);
    }
}
