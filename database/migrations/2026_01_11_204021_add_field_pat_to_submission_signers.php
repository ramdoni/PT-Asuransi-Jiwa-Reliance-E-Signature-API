<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldPatToSubmissionSigners extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('submission_signers', function (Blueprint $table) {
            $table->string('pat')->nullable()->after('is_signed');
            $table->string('reason')->nullable()->after('pat');
            $table->string('location')->nullable()->after('reason');
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
