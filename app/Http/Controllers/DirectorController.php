<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use App\Models\SubmissionLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Mail\NotificationMail;
use Illuminate\Support\Facades\Mail;

class DirectorController extends Controller
{
    public function process(Request $request)
    {
        $this->validate($request, [
            'link_code'=> 'required'
        ]);

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
            $link_step = $request->director==1 ? 3 : 4;

            $submission->update([
                'link_code' => $link_code,
                'link_step' => $link_step,
                'status' => Submission::STATUS_DIREKSI_2,
                'link_expired' => date('Y-m-d H:i:s',strtotime("+1 day"))
            ]);

            if($request->director==2){
                $submission->update([
                    'status' => Submission::STATUS_SIGNED,
                    'link_expired' => null,
                    'link_step' => $link_step,
                    'link_code' => null,
                ]);
            }else{
                $link = env('FRONTEND_URL') ."/preview-dokument/{$link_code}";
                foreach(User::where('position',User::IS_DIRECTOR_1)->get() as $item){
                    if(!$item->email) continue;
                    $subject = "{$submission->perihal} - Signed requested by Relisign";
                    $message = "<p> Department ". (isset($submission->divisi->name) ? $submission->divisi->name ." ({$submission->divisi->email}) " : '')  ." has requested a signature</p>";
                    $message .= "<p>Note : {$submission->message}</p>";
                    $message .= "<p>Review Document : {$link}</p>";

                    Mail::to($item->email)->send(new NotificationMail($subject, $message));
                }
            }
        }
        
        $status = $request->director==1 ? Submission::STATUS_DIREKSI_1 : Submission::STATUS_DIREKSI_2;

        SubmissionLog::create([
            'submission_id'=> $submission->id,
            'status'=> $status,
            'title'=> "The document has been signed by the director {$request->director}",
            // 'email'=> Auth::user()->email
        ]);

        return response()->json(['status'=>'success'],200);
    }
}