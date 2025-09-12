<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage; // For file storage

class SubmissionController extends Controller
{

    public function index()
    {
        $data = Submission::orderBy('id','DESC')->paginate(100);

        $data = $data->getCollection()->transform(function ($item) {
            $item->dokumen = $item->dokumen 
                ? asset($item->dokumen) 
                : asset('no_image.jpg');
            return $item;
        });

        return response()->json(['status'=>'success','data'=>$data],200);
    }
    public function store(Request $request)
    {
        $this->validate($request, [
            'kategori_surat' => 'required',
            'judul_dokumen' => 'required',
            'perihal' => 'required',
            'jenis_dokumen_id' => 'required',
            'divisi_id' => 'required',
            'tanggal_diterima' => 'required',
            'no_dokumen' => 'required',
            'tujuan_tanda_tangan_id' => 'required',
            'signatory_id' => 'required',
            'jenis_tanda_tangan' => 'required',
            'dokumen' => 'required|file|mimes:jpeg,png,pdf|max:10048',
            'catatan' => 'required'
        ]);

        $submission = Submission::create([
            'kategori_surat' => $request->kategori_surat,
            'judul_dokumen' => $request->judul_dokumen,
            'perihal' => $request->perihal,
            'jenis_dokumen_id' => $request->jenis_dokumen_id,
            'divisi_id' => $request->divisi_id,
            'tanggal_diterima' =>$request->tanggal_diterima,
            'no_dokumen' => $request->no_dokumen,
            'tujuan_tanda_tangan_id' => $request->tujuan_tanda_tangan_id,
            'signatory_id' => $request->signatory_id,
            'jenis_tanda_tangan' => $request->jenis_tanda_tangan,
            'dokumen' => $request->dokumen,
            'catatan' => $request->catatan
        ]);

        $file = $request->file('dokumen');
        $path = Storage::disk('public')->putFile('uploads', $file);
        $fileName = time() . '.' . $file->extension();
        
        $path = $file->storeAs("uploads/{$submission->id}", $fileName, 'public');
        
        $submission->update(['dokumen'=>$path]);

        return response()->json(['status'=>'success'],200);
    }
    
}
