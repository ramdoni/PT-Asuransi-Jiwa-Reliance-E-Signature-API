<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableSubmission extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->boolean('kategori_surat')->default(1);
            $table->string('reply_referensi_surat')->nullable();
            $table->string('reply_judul_dokumen')->nullable();
            $table->string('reply_perihal')->nullable();
            $table->integer('reply_jenis_dokumen_id')->nullable();
            $table->string('reply_pengirim_surat')->nullable();
            $table->string('reply_no_surat')->nullable();
            $table->date('reply_tanggal_surat_diterima')->nullable();
            $table->string('judul_dokumen')->nullable();
            $table->string('perihal')->nullable();
            $table->integer('jenis_dokumen_id')->nullable();
            $table->integer('divisi_id')->nullable();
            $table->date('tanggal_diterima')->nullable();
            $table->string('no_dokumen')->nullable();
            $table->integer('tujuan_tanda_tangan_id')->nullable();
            $table->integer('signatory_id')->nullable();
            $table->boolean('jenis_tanda_tangan')->default(1);
            $table->text('dokumen')->nullable();
            $table->text('catatan')->nullable();
            $table->string('no_pengajuan',200)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('submission');
    }
}
