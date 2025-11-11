<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use App\Models\SubmissionLog;
use App\Models\User;
use Illuminate\Http\Request;
use App\Mail\NotificationMail;
use App\Models\SubmissionSigner;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;

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
        if (!$submission){
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
            
            $signer = SubmissionSigner::where(['submission_id'=>$submission->id,'is_signed'=>0])->first();
            if($signer){
                if($signer->phone){
                    $message  = "*REVIEW REQUESTER BY RELISIGN*\n\n";
                    $message .= "*PERIHAL* : {$submission->perihal}\n";
                    $message .= "*DEPARTMENT* ". (isset($submission->divisi->name) ? $submission->divisi->name ." ({$submission->divisi->email}) " : '')  ." has requested a signature\n";
                    $message .= "*NOTE* : {$submission->message}\n";
                    $message .= "*REVIEW LINK* : {$link}\n";
                    
                    Http::withHeaders([
                        'Content-Type' => 'application/json',
                    ])->post('http://wa-center.entigi.co.id/v1/wa/send', [
                        'phone' => $signer->phone,
                        'message' => $message,
                    ]);
                    $signer->update(['link_code'=>$link_code]);
                }
                if($signer->email){
                    try {
                        $subject = "{$submission->perihal} - Review requested by Relisign";
                        $message = "<p> Department ". (isset($submission->divisi->name) ? $submission->divisi->name ." ({$submission->divisi->email}) " : '')  ." has requested a signature</p>";
                        $message .= "<p>Note : {$submission->message}</p>";
                        $message .= "<p>Review Link : {$link}</p>";
                        Mail::to($signer->email)->send(new NotificationMail($subject, $message));

                    } catch (\Exception $e) {}
                }
            }
            
            return response()->json(['status'=>'success'],200);
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