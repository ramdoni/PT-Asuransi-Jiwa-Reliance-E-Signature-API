<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage; // For file storage

class SubmissionController extends Controller
{

    public function index()
    {
        $data = Submission::selectRaw("submissions.*, DATE_FORMAT(submissions.created_at, '%d-%m-%Y') as submitted_at,jenis_dokuments.name as jenis_dokumen_name,divisi.name as devisi_name,tujuan_tanda_tangans.name as tujuan_tanda_tangan_name")
                ->orderBy('submissions.id','DESC')
                ->join('jenis_dokuments','jenis_dokuments.id','=','submissions.jenis_dokumen_id')
                ->join('divisi','divisi.id','=','submissions.divisi_id')
                ->join('tujuan_tanda_tangans','tujuan_tanda_tangans.id','=','submissions.tujuan_tanda_tangan_id')
                ;

        if(Auth::user()->position == User::IS_REQUESTER) $data->where('submissions.user_id', Auth::user()->id);

        $data = $data->paginate(100)->getCollection()->transform(function ($item) {
            $item->dokumen = $item->dokumen 
                ? asset($item->dokumen) 
                : asset('no_image.jpg');
            
            $item->status_name = isset(Submission::$STATUS[$item->status]) ? Submission::$STATUS[$item->status] : 'Draft';

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
            // 'signatory_id' => 'required',
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
            'catatan' => $request->catatan,
            'user_id'=>Auth::user()->id
        ]);

        $file = $request->file('dokumen');
        $path = Storage::disk('public')->putFile('uploads', $file);
        $fileName = time() . '.' . $file->extension();
        
        $path = $file->storeAs("uploads/{$submission->id}", $fileName, 'public');
        
        $submission->update(['dokumen'=>$path]);

        return response()->json(['status'=>'success'],200);
    }

    public function delete($id)
    {
        try {
            $submission = Submission::find($id);

            if (!$submission) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }
            
            if($submission->user_id == Auth::user()->id)  $submission->delete();

            $path = base_path('public/' . $submission->dokumen);
            if ($submission->dokumen && file_exists($path)) {
                @unlink($path);
            }

            $submission->delete();

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
