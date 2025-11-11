<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use App\Models\SubmissionLog;
use App\Models\User;
use Illuminate\Http\Request;
use App\Mail\NotificationMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use App\Models\SubmissionSigner;

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
            $checkSigner = SubmissionSigner::where(['link_code'=> $request->link_code,'submission_id'=>$submission->id])->first();
            if($checkSigner) $checkSigner->update(['link_code'=>NULL,'is_signed'=>1]);

            $link_code = bin2hex(random_bytes(10));    
            $submission->update([
                'link_code' => $link_code,
                'status' => Submission::STATUS_DIREKSI_2,
                'link_expired' => date('Y-m-d H:i:s',strtotime("+1 day"))
            ]);
            
            $checkSigner = SubmissionSigner::where(['is_signed'=>0,'submission_id'=>$submission->id])->first();
            if($checkSigner){
                
                $checkSigner->update(['link_code'=>$link_code]);

                $link = env('FRONTEND_URL') ."/preview-dokument/{$link_code}";
                $message  = "*REVIEW REQUESTER BY RELISIGN*\n\n";
                $message .= "*PERIHAL* : {$submission->perihal}\n";
                $message .= "*DEPARTMENT* ". (isset($submission->divisi->name) ? $submission->divisi->name ." ({$submission->divisi->email}) " : '')  ." has requested a signature\n";
                $message .= "*NOTE* : {$submission->message}\n";
                $message .= "*REVIEW LINK* : {$link}\n";

                Http::withHeaders([
                    'Content-Type' => 'application/json',
                ])->post('http://wa-center.entigi.co.id/v1/wa/send', [
                    'phone' => $checkSigner->phone,
                    'message' => $message,
                ]);
                try {
                    if($checkSigner->email){
                        $subject = "{$submission->perihal} - Signed requested by Relisign";
                        $message = "<p> Department ". (isset($submission->divisi->name) ? $submission->divisi->name ." ({$submission->divisi->email}) " : '')  ." has requested a signature</p>";
                        $message .= "<p>Note : {$submission->message}</p>";
                        $message .= "<p>Review Document : {$link}</p>";

                        Mail::to($checkSigner->email)->send(new NotificationMail($subject, $message));
                    }
                } catch (\Exception $e) {}
            }else{
                $path = $submission->dokumen;
                $base64 = file_get_contents($path);
                $base64 = base64_encode($base64);
                $signers = SubmissionSigner::where(['submission_id'=>$submission->id])->whereNotNull('page')->get();
                $position =[];
                foreach($signers as $k => $signer){
                    $position[$k] = [
                        "x"=>(int)$signer->x,
                        "y"=>(int)$signer->y,
                        "page"=>(int)$signer->page,
                        "w" => 130,
                        "h" => 50
                    ];
                }
                
                $result = stampDocument($base64, $submission->judul_dokumen, $submission->perihal,$position,$submission);
                if($signer){
                    if(isset($result['success'])){
                        $signer->update([
                            'file_signer' => $result['file_path']
                        ]);
                    }else{
                        return response()->json([
                            'status' => 'error',
                            'message' => $result
                        ], 404);
                    }
                }
                $submission->update([
                    'status' => Submission::STATUS_SIGNED,
                    'link_expired' => null,
                    'link_code' => null,
                    'dokumen_signed'=> isset($result['file_path']) ? $result['file_path'] : ''
                ]);
            }
        }
    
        SubmissionLog::create([
            'submission_id'=> $submission->id,
            'status'=> Submission::STATUS_DIREKSI_1,
            'title'=> "The document has been signed by the director {$checkSigner->name}",
            'email'=> $checkSigner->email
        ]);

        return response()->json(['status'=>'success'],200);
    }
}