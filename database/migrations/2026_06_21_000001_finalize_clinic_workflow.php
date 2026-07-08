<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FinalizeClinicWorkflow extends Migration
{
    public function up()
    {
        Schema::create('counter_services', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('student_complaint_id')->unique();
            $table->unsignedBigInteger('patient_id');
            $table->text('remedy_given');
            $table->string('quantity')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('handled_by');
            $table->string('outcome');
            $table->timestamp('handled_at');
            $table->timestamps();

            $table->foreign('student_complaint_id')->references('id')->on('student_complaints')->onDelete('cascade');
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade');
            $table->foreign('handled_by')->references('id')->on('users')->onDelete('restrict');
        });

        Schema::create('consultations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('student_complaint_id')->unique();
            $table->unsignedBigInteger('patient_id');
            $table->string('service_needed');
            $table->string('priority');
            $table->text('nurse_notes')->nullable();
            $table->unsignedBigInteger('forwarded_by');
            $table->timestamp('forwarded_at');
            $table->string('status')->default('Pending Consultation');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedBigInteger('doctor_id')->nullable();
            $table->text('diagnosis')->nullable();
            $table->text('treatment')->nullable();
            $table->text('prescription')->nullable();
            $table->text('doctor_notes')->nullable();
            $table->date('follow_up_date')->nullable();
            $table->string('attachment')->nullable();
            $table->timestamps();

            $table->foreign('student_complaint_id')->references('id')->on('student_complaints')->onDelete('cascade');
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade');
            $table->foreign('forwarded_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('doctor_id')->references('id')->on('users')->onDelete('set null');
        });

        Schema::table('medical_records', function (Blueprint $table) {
            $table->unsignedBigInteger('counter_service_id')->nullable()->after('student_complaint_id');
            $table->unsignedBigInteger('consultation_id')->nullable()->after('counter_service_id');
            $table->string('record_type')->nullable()->after('consultation_status');
            $table->string('source')->nullable()->after('record_type');
            $table->text('description')->nullable()->after('source');
            $table->string('outcome')->nullable()->after('description');
            $table->unsignedBigInteger('created_by')->nullable()->after('attending_staff_id');

            $table->foreign('counter_service_id')->references('id')->on('counter_services')->onDelete('set null');
            $table->foreign('consultation_id')->references('id')->on('consultations')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('medical_records', function (Blueprint $table) {
            $table->dropForeign(['counter_service_id']);
            $table->dropForeign(['consultation_id']);
            $table->dropForeign(['created_by']);
            $table->dropColumn(['counter_service_id', 'consultation_id', 'record_type', 'source', 'description', 'outcome', 'created_by']);
        });

        Schema::dropIfExists('consultations');
        Schema::dropIfExists('counter_services');
    }
}
