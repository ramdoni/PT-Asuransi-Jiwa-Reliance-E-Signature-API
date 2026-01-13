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
    public function delete($id)
    {
        $item = TujuanTandaTangan::find($id);
        if(!$item){
            return response()->json(['status'=>'error','message'=>'Data not found'],404);
        }

        $item->delete();

        return response()->json(['status'=>'success'],200);
    }
}
