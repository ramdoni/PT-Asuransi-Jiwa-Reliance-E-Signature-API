<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Submission;
use App\Mail\NotificationMail;
use Illuminate\Support\Facades\Mail;
class DashboardController extends Controller
{

    public function index()
    {
        $counts = Submission::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total','status')
            ->toArray();

        $result['total_dokument_aktif'] = ($counts[Submission::STATUS_SIGNED] ?? 0) + 
                                          ($counts[Submission::STATUS_LEGAL_REVIEW] ?? 0) + 
                                          ($counts[Submission::STATUS_PENDING_SIGNATURE] ?? 0) + 
                                          ($counts[Submission::STATUS_OVERDUE] ?? 0) + 
                                          ($counts[Submission::STATUS_DIREKSI_1] ?? 0)+
                                          ($counts[Submission::STATUS_DIREKSI_2] ?? 0);
        $result['total_dokument_overdue'] = $counts[Submission::STATUS_OVERDUE] ?? 0;
        $result['total_ditandangani'] = $counts[Submission::STATUS_SIGNED] ?? 0;
        $result['total_legal_review'] = $counts[Submission::STATUS_LEGAL_REVIEW] ?? 0;
        $result['total_direksi'] = ($counts[Submission::STATUS_DIREKSI_1] ?? 0)+($counts[Submission::STATUS_DIREKSI_1] ?? 0);
        $result['total_pengiriman_eksternal'] = 0;
        $result['total_bulan_ini'] = Submission::where('status','<>',Submission::STATUS_DRAFT)
                                                ->whereDate('created_at',date('Y-m-d'))
                                                ->count();

        return response()->json([
            'status' => 'success',
            'data' => $result
        ], 200);
    }    

    public function sendNotification()
    {
        $to = 'doni.enginer@gmail.com';
        $subject = 'Notifikasi Dokumen Baru';
        $message = 'Ada dokumen baru yang menunggu review Anda. Silakan cek di dashboard.';

        try {
            Mail::to($to)->send(new NotificationMail($subject, $message));
            return response()->json(['status' => 'success', 'message' => 'Email terkirim']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}
