<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use App\Models\SubmissionLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Mail\NotificationMail;
use Illuminate\Support\Facades\Mail;

class LegalController extends Controller
{
    public function process(Request $request)
    {
        $this->validate($request, [
            'catatan' => 'required',
            'status'=>'required'
        ]);

        if(isset($request->submission_id)) $submission = Submission::find($request->submission_id);
        if(isset($request->link_code)) $submission = Submission::where('link_code',$request->link_code)->first();
        if (!$submission) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        $submission->update([
            'status'=> $request->status==2 ? Submission::STATUS_REJECT_LEGAL : Submission::STATUS_DIREKSI_1
        ]);

        if(isset($request->link_code)){
            $link_code = bin2hex(random_bytes(10));    
            $submission->update([
                'link_code' => $link_code,
                'link_step' => 2,
                'link_expired' => date('Y-m-d H:i:s',strtotime("+1 day"))
            ]);

            $link = env('FRONTEND_URL') ."/preview-dokument/{$link_code}";
            foreach(User::where('position',User::IS_DIRECTOR_1)->get() as $item){
                if(!$item->email) continue;

                $subject = "{$submission->perihal} - Review requested by Relisign";
                $message = "<p> Department ". (isset($submission->divisi->name) ? $submission->divisi->name ." ({$submission->divisi->email}) " : '')  ." has requested a signature</p>";
                $message .= "<p>Note : {$submission->message}</p>";
                $message .= "<p>Review Link : {$link}</p>";

                Mail::to($item->email)->send(new NotificationMail($subject, $message));
            }
        }
        
        SubmissionLog::create([
            'submission_id'=> $submission->id,
            'status'=> $request->status==1 ? Submission::STATUS_REJECT_LEGAL : Submission::STATUS_APPROVE_LEGAL,
            'title'=> 'Document Review By Legal : '. $request->catatan,
            // 'email'=> Auth::user()->email
        ]);

        return response()->json(['status'=>'success'],200);
    }
}