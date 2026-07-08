<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePrescriptionsTable extends Migration
{
    public function up()
    {
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('consultation_id')->unique();
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('doctor_id');
            $table->string('prescription_number')->unique();
            $table->string('prescription_type');
            $table->json('medications')->nullable();
            $table->text('additional_instructions')->nullable();
            $table->date('follow_up_date')->nullable();
            $table->string('pdf_path')->nullable();
            $table->timestamps();

            $table->foreign('consultation_id')->references('id')->on('consultations')->onDelete('cascade');
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade');
            $table->foreign('doctor_id')->references('id')->on('users')->onDelete('restrict');
        });

        Schema::table('medical_records', function (Blueprint $table) {
            $table->unsignedBigInteger('prescription_id')->nullable()->unique()->after('consultation_id');
            $table->foreign('prescription_id')->references('id')->on('prescriptions')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('medical_records', function (Blueprint $table) {
            $table->dropForeign(['prescription_id']);
            $table->dropColumn('prescription_id');
        });

        Schema::dropIfExists('prescriptions');
    }
}
