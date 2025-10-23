<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Models\SignedDocument; 

if (!function_exists('stampDocument')) {
    /**
     * Kirim dokumen ke API Stamp Service
     *
     * @param string $documentBase64  Base64 dari file PDF
     * @param string $title           Judul dokumen
     * @param string $reason          Alasan tanda tangan
     * @param array|null $positions   Posisi tanda tangan (optional)
     * @return array|string           Response API
     */
    function stampDocument($documentBase64, $title = 'Document', $reason = 'Approval', $positions = null,$submission)
    {
        try {
            $positions = $positions ?? [
                [
                    "page" => 1,
                    "x" => 100,
                    "y" => 100,
                    "w" => 130,
                    "h" => 50
                ]
            ];

            $payload = [
                "pat" => "4kiPE4bsXch4XzXUYsvUB9Wm6yPD48u5Onzh",
                "location" => "Indonesia, Jakarta",
                "reason" => $reason,
                "visible" => true,
                "hideDate" => false,
                "usingQR" => true,
                "signatureImage" => "",
                "document" => $documentBase64,
                "title" => $title,
                "signPositions" => $positions
            ];

            $response = Http::withHeaders([
                'api-key' => env('XSIGNATURE_API_KEY'),
            ])->post(env('XSIGNATURE_STAMP').'/stamp/document', $payload);

            if (!$response->successful()) {
                return [
                    'success' => false,
                    'message' => 'Failed to connect to stamp service',
                    'response' => $response->body()
                ];
            }

            $data = $response->json();

            if ($data['errorCode'] != 0 || empty($data['signedDocument'])) {
                return [
                    'success' => false,
                    'message' => 'Stamping failed or document not found',
                    'response' => $data
                ];
            }

            // Decode base64 → binary PDF
            $pdfContent = base64_decode($data['signedDocument']);

            // Simpan ke storage
            $fileName = 'signed.pdf';
            $filePath = "public/upload/{$submission->id}/" . $fileName;
            Storage::put($filePath, $pdfContent);

            return [
                'success' => true,
                'message' => 'Document signed and saved successfully',
                'file_path' => $filePath,
                'file_name' => $fileName
            ];

        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}