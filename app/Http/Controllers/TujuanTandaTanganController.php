<?php

namespace App\Http\Controllers;

use App\Models\TujuanTandaTangan;
use Illuminate\Http\Request;

class TujuanTandaTanganController extends Controller
{

    public function index()
    {
        $data = TujuanTandaTangan::get();

        return response()->json(['status'=>'success','data'=>$data],200);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
        ]);

        TujuanTandaTangan::create([
            'name'=>$request->name,
        ]);
        
        return response()->json(['status'=>'success'],200);
    }
}
