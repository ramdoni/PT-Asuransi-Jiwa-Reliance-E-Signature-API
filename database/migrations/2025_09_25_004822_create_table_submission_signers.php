<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableSubmissionSigners extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('submission_signers', function (Blueprint $table) {
            $table->id();
            $table->integer('submission_id')->nullable();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->text('link_code')->nullable();
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
        Schema::dropIfExists('submission_signers');
    }
}
