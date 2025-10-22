<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldCoordinateToSubmissionSigners extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('submission_signers', function (Blueprint $table) {
            $table->string('x')->nullable();
            $table->string('y')->nullable();
            $table->string('page')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('submission_signers', function (Blueprint $table) {
            //
        });
    }
}
