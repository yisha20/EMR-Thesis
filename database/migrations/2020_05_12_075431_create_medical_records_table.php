<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMedicalRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('medical_records', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('patient_id');
            $table->string('performed_service')->nullable();
            $table->date('date_of_consultation')->nullable();
            $table->time('time_of_consultation')->nullable();
            $table->string('chief_complaint')->nullable();
            $table->json('vital_signs')->nullable();
            $table->string('nurse_assigned')->nullable();
            $table->string('history_of_present_illness')->nullable();
            $table->string('medication_taken')->nullable();
            $table->string('findings')->nullable();
            $table->string('recommendation')->nullable();
            $table->string('diagnosis')->nullable();
            $table->string('file')->nullable();
            $table->string('attending_physician')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('medical_records');
    }
}
