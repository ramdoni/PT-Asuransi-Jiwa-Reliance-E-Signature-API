<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use App\Models\SubmissionLog;
use App\Models\SubmissionSigner;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Mail\NotificationMail;
use Illuminate\Support\Facades\Mail;

class SubmissionController extends Controller
{
    public function index()
    {
        $data = Submission::selectRaw("submissions.*, DATE_FORMAT(submissions.due_date, '%d-%m-%Y') as due_date, DATE_FORMAT(submissions.created_at, '%d-%m-%Y') as submitted_at,jenis_dokuments.name as jenis_dokumen_name,divisi.name as divisi_name,tujuan_tanda_tangans.name as tujuan_tanda_tangan_name")
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
            $item->status_class = 'yellow';
            
            if($item->status==Submission::STATUS_DRAFT) $item->status_class = 'yellow';
            if($item->status==Submission::STATUS_LEGAL_REVIEW) $item->status_class = 'purple';
            if($item->status==Submission::STATUS_PENDING_SIGNATURE) $item->status_class = 'blue';
            if($item->status==Submission::STATUS_SIGNED) $item->status_class = 'green';
            if($item->status==Submission::STATUS_REJECT) $item->status_class = 'red';
            
            return $item;
        });

        return response()->json(['status'=>'success','data'=>$data],200);
    }

    public function logs($id)
    {
        $logs = SubmissionLog::selectRaw("submission_logs.*, DATE_FORMAT(created_at, '%d/%m/%Y') as created_date, DATE_FORMAT(created_at, '%h:%i') as created_time")
                    ->where('submission_id',$id)->get();
        
        return response()->json([
            'status' => 'success',
            'data' => $logs
        ], 200);
    }

    public function show($id)
    {
        $submission = Submission::selectRaw("submissions.*, DATE_FORMAT(submissions.due_date, '%d-%m-%Y') as due_date, DATE_FORMAT(submissions.created_at, '%d-%m-%Y') as submitted_at,jenis_dokuments.name as jenis_dokumen_name,divisi.name as divisi_name,tujuan_tanda_tangans.name as tujuan_tanda_tangan_name")
                            ->where('submissions.id',$id)
                            ->join('jenis_dokuments','jenis_dokuments.id','=','submissions.jenis_dokumen_id')
                            ->join('divisi','divisi.id','=','submissions.divisi_id')
                            ->join('tujuan_tanda_tangans','tujuan_tanda_tangans.id','=','submissions.tujuan_tanda_tangan_id')
                            ->first();
         if (!$submission) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data tidak ditemukan'
            ], 404);
        }
        
        $submission->status_name = isset(Submission::$STATUS[$submission->status]) ? Submission::$STATUS[$submission->status] : 'Draft';
        $submission->status_class = 'yellow';
        
        if($submission->status == Submission::STATUS_DRAFT) $submission->status_class = 'yellow';
        if($submission->status == Submission::STATUS_LEGAL_REVIEW) $submission->status_class = 'purple';
        if($submission->status == Submission::STATUS_PENDING_SIGNATURE) $submission->status_class = 'blue';
        if($submission->status == Submission::STATUS_SIGNED) $submission->status_class = 'green';
        if($submission->status == Submission::STATUS_REJECT) $submission->status_class = 'red';

        $submission->dokumen_absolute = $submission->dokumen;
        $submission->dokumen = asset($submission->dokumen);

        $assigner = SubmissionSigner::where(['submission_id'=>$submission->id])->get();

        return response()->json(['status'=>'success','data'=>$submission,'assigner'=>$assigner],200);
    }

    public function assigner($id)
    {
        $assigner = SubmissionSigner::where(['submission_id'=>$id])->get();

        if (!$assigner) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        return response()->json(['status'=>'success','data'=>$assigner],200);
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
            'due_date' => 'required',
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
            'due_date' =>$request->due_date,
            'no_dokumen' => $request->no_dokumen,
            'tujuan_tanda_tangan_id' => $request->tujuan_tanda_tangan_id,
            // 'signatory_id' => $request->signatory_id,
            'jenis_tanda_tangan' => $request->jenis_tanda_tangan,
            'dokumen' => $request->dokumen,
            'catatan' => $request->catatan,
            'user_id'=>Auth::user()->id,
            'submission_step'=>2
        ]);

        $file = $request->file('dokumen');
        $path = Storage::disk('public')->putFile('uploads', $file);
        $fileName = time() . '.' . $file->extension();
        
        $path = $file->storeAs("uploads/{$submission->id}", $fileName, 'public');
        
        $submission->update(['dokumen'=>$path]);

        return response()->json(['status'=>'success','data'=>$submission],200);
    }

    public function finish(Request $request)
    {
        $this->validate($request, [
            'message' => 'required',
            'id' => 'required'
        ]);

        $submission = Submission::find($request->id);

        if (!$submission) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data tidak ditemukan',
                'data'=>$request->all(),
                'id'=>$request->id
            ], 404);
        }

        $link_code = bin2hex(random_bytes(10));

        $submission->update([
            'email'=>$request->email,
            'email_cc'=>$request->email_cc,
            'subject'=>$request->subject,
            'message'=>$request->message,
            'link_code' => $link_code,
            'link_step'=>1,
            'link_expired'=>date('Y-m-d H:i:s',strtotime("+1 day"))
            // 'status'=>Submission::STATUS_LEGAL_REVIEW,
            // 'submission_step'=>5
        ]);

        $link = env('FRONTEND_URL') ."/preview-dokument/{$link_code}";

        SubmissionLog::create([
            'submission_id'=>$submission->id,
            'status'=>Submission::STATUS_LEGAL_REVIEW,
            'title'=>'Document Upload By Corporation Secretary',
            'email'=> Auth::user()->email
        ]);

        try {
            foreach(User::where('position',User::IS_LEGAL)->get() as $item){
                if(!$item->email) continue;

                $subject = "{$submission->perihal} - Review requested by Relisign";
                $message = "<p> Department ". (isset($submission->divisi->name) ? $submission->divisi->name ." ({$submission->divisi->email}) " : '')  ." has requested a signature</p>";
                $message .= "<p>Note : {$submission->message}</p>";
                $message .= "<p>Review Link : {$link}</p>";

                Mail::to($item->email)->send(new NotificationMail($subject, $message));
            }
        } catch (\Exception $e) {
            return response()->json(['status'=>'success','message'=>$e->getMessage()],400);
        }
        
        return response()->json(['status'=>'success'],200);
    }
    public function submitPlaceFields(Request $request)
    {
        try {
            $this->validate($request,[
                'id' => 'required|integer|exists:submissions,id'
            ]);
            $submission = Submission::find($request->id);
            if (!$submission) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data tidak ditemukan',
                    'data'=>$request->all(),
                    'id'=>$request->id
                ], 404);
            }
            $submission->update(['submission_step'=>4]);
            return response()->json(['status'=>'success'],200);
        }catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan data: '.$e->getMessage()
            ], 500);
        }
    }

    public function submitSigner(Request $request)
    {
        $data = $request->json()->all();
        try {
            $validator = Validator::make($data, [
                'id' => 'required|integer|exists:submissions,id',
                'items' => 'required|array|min:1',
                'items.*.name' => 'required|string',
                'items.*.email' => 'required|email',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            // Ambil data
            $id = $data['id'];
            $items = $data['items'];
            
            $submission = Submission::find($id);

            if (!$submission) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data tidak ditemukan',
                    'data'=>$request->all(),
                    'id'=>$request->id
                ], 404);
            }

            foreach($items as $item){
                SubmissionSigner::updateOrCreate([
                    'submission_id' => $submission->id,
                    'email' => $item['email'],
                ],[
                    'submission_id' => $submission->id,
                    'name' => $item['name'],
                    'email' => $item['email'],
                ]);
            }
            
            $submission->update(['submission_step'=>3]);

            return response()->json(['status'=>'success','data'=>$submission],200);
        }catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan data: '.$e->getMessage()
            ], 500);
        }
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

    public function validateLink($id)
    {
        try {
            $submission = Submission::selectRaw("submissions.*, DATE_FORMAT(submissions.due_date, '%d-%m-%Y') as due_date, DATE_FORMAT(submissions.created_at, '%d-%m-%Y') as submitted_at,jenis_dokuments.name as jenis_dokumen_name,divisi.name as divisi_name,tujuan_tanda_tangans.name as tujuan_tanda_tangan_name")
                            ->join('jenis_dokuments','jenis_dokuments.id','=','submissions.jenis_dokumen_id')
                            ->join('divisi','divisi.id','=','submissions.divisi_id')
                            ->join('tujuan_tanda_tangans','tujuan_tanda_tangans.id','=','submissions.tujuan_tanda_tangan_id')
                            ->where('submissions.link_code',$id)
                            ->first();

            if (!$submission) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }
            
            $submission->status_name = isset(Submission::$STATUS[$submission->status]) ? Submission::$STATUS[$submission->status] : 'Draft';
            $submission->status_class = 'yellow';
            
            if($submission->status == Submission::STATUS_DRAFT) $submission->status_class = 'yellow';
            if($submission->status == Submission::STATUS_LEGAL_REVIEW) $submission->status_class = 'purple';
            if($submission->status == Submission::STATUS_PENDING_SIGNATURE) $submission->status_class = 'blue';
            if($submission->status == Submission::STATUS_SIGNED) $submission->status_class = 'green';
            if($submission->status == Submission::STATUS_REJECT) $submission->status_class = 'red';

            $submission->dokumen_absolute = $submission->dokumen;
            $submission->dokumen = asset($submission->dokumen);

            if(strtotime($submission->link_expired) <= strtotime(date('Y-m-d H:i:s'))){
                return response()->json([
                    'status' => 'error',
                    'message' => 'Link expired'
                ], 404);
            }
            
            if (!$submission) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }
            
            return response()->json([
                'status' => 'success',
                'message' => 'Data berhasil',
                'data' => $submission->toArray()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus data: '.$e->getMessage()
            ], 500);
        }
    }
}
