<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Submission extends Model
{
    public $KATEGORY_SURAT = [1=>'SURAT BARU',2=>'SURAT BALASAN'];
    public $JENIS_TANDA_TANGAN = [1=>'DIGITAL',2=>'BASAH'];
    public static $STATUS = [1=>'Draft',2=>'Signed',3=>'Legal Review',4=>'Pending Signature',5=>'Overdue',6=>'Direksi 1',7=>'Direksi 2',8=>'Reject','Reject Legal'=>9,'Approve Legal'=>10];
    public const KATEGORI_SURAT_BARU = 1;
    public const KATEGORI_SURAT_BALASAN = 2;
    public const JENIS_TANDA_TANGAN_DIGITAL = 1;
    public const JENIS_TANDA_TANGAN_BASAH = 2;
    public const STATUS_DRAFT = 1;
    public const STATUS_SIGNED = 2;
    public const STATUS_LEGAL_REVIEW = 3;
    public const STATUS_PENDING_SIGNATURE = 4;
    public const STATUS_OVERDUE = 5;
    public const STATUS_DIREKSI_1 = 6;
    public const STATUS_DIREKSI_2 = 7;
    public const STATUS_REJECT = 8;
    public const STATUS_REJECT_LEGAL = 9;
    public const STATUS_APPROVE_LEGAL = 10;
    protected $guarded = ['id'];
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Ambil tanggal hari ini
            $tanggal = date('Ymd');
            $time = date('Hi');
            // Hitung nomor urut hari ini
            $lastTransaksi = self::whereDate('created_at', date('Y-m-d'))
                ->orderBy('id', 'desc')
                ->first();
            $lastNumber = $lastTransaksi
                ? (int) substr($lastTransaksi->no_transaksi, -4)
                : 0;
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
            // Set no_transaksi otomatis
            $model->no_pengajuan = "{$time}-{$tanggal}-{$newNumber}";
        });
    }

    public function divisi()
    {
        return $this->hasOne(Divisi::class,'id','divisi_id');
    }
}
