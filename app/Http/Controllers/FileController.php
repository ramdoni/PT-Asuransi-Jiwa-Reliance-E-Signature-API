<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use App\Models\SubmissionSigner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileController extends Controller
{
    public function showPdf($id)
    {
        $submission = Submission::find($id);
        $path = $submission->dokumen;

        if (!file_exists($path)) {
            return response()->json(['message' => 'File not found','file'=>$submission->dokumen], 404);
        }

        // Buat streamed response manual (karena Lumen tidak punya response()->file)
        $response = new StreamedResponse(function () use ($path) {
            $stream = fopen($path, 'rb');
            fpassthru($stream);
            fclose($stream);
        });

        // Tambahkan header agar browser bisa render PDF dan lolos CORS
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization');

        return $response;
    }

    public function testStamp($id)
    {
        $submission = Submission::find($id);
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
        
        $result = stampDocument($base64, 'Testing Document', 'I Approve this document',$position,$submission);

        if($signer){
            if($result['success']){
                $signer->update([
                    'file_signer' => $result['file_path']
                ]);
            }
        }

        return response()->json($result);
    }
}