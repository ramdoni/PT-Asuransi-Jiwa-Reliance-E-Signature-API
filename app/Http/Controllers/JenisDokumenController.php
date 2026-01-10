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
    public function update($id,Request $request)
    {
        $this->validate($request, [
            'name' => 'required'
        ]);

        $jenisDokumen = JenisDokument::find($id);

        if (!$jenisDokumen){
            return response()->json(['error' => 'Data tidak ditemukan'], 401);
        }

        $jenisDokumen->update([
            'name'=>$request->name
        ]);

        return response()->json(['status'=>'success'],200);
    }

    public function delete($id)
    {
        try {
            $jenisDokumen = JenisDokument::find($id);
            
            if (!$jenisDokumen) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }
            
            $user->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Data berhasil dihapus'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus data: '.$e->getMessage()
            ], 500);
        }
    }
}
