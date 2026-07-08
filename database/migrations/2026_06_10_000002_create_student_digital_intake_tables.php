<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateStudentDigitalIntakeTables extends Migration
{
    public function up()
    {
        DB::table('roles')->updateOrInsert(['name' => 'Student'], ['name' => 'Student']);

        Schema::create('students', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->unique();
            $table->string('student_id_number')->unique();
            $table->string('college_department');
            $table->string('contact_number');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('student_complaints', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('patient_id')->nullable();
            $table->unsignedBigInteger('medical_record_id')->nullable();
            $table->string('student_id_number');
            $table->string('student_name');
            $table->string('chief_complaint');
            $table->text('symptoms_description');
            $table->string('urgency_level');
            $table->string('status')->default('Pending');
            $table->string('attachment')->nullable();
            $table->timestamp('submitted_at');
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('consultation_started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('diagnosis')->nullable();
            $table->text('treatment')->nullable();
            $table->text('prescription')->nullable();
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('set null');
            $table->foreign('medical_record_id')->references('id')->on('medical_records')->onDelete('set null');
            $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('set null');
        });

        Schema::create('complaint_status_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('student_complaint_id');
            $table->unsignedBigInteger('changed_by')->nullable();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('student_complaint_id')->references('id')->on('student_complaints')->onDelete('cascade');
            $table->foreign('changed_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('complaint_status_logs');
        Schema::dropIfExists('student_complaints');
        Schema::dropIfExists('students');
        DB::table('roles')->where('name', 'Student')->delete();
    }
}
