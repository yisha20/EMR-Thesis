<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CompleteStudentIntakeWorkflow extends Migration
{
    public function up()
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->string('email')->nullable()->after('phone_number');
            $table->string('civil_status')->nullable()->after('gender');
            $table->timestamp('date_registered')->nullable()->after('updated_by');
            $table->timestamp('last_reviewed_at')->nullable()->after('date_registered');
        });

        DB::table('patients')
            ->whereNotNull('status')
            ->whereNotIn('status', ['Active', 'Inactive'])
            ->update([
                'civil_status' => DB::raw('status'),
                'status' => 'Active',
            ]);

        DB::table('patients')->whereNull('status')->update(['status' => 'Active']);
        DB::table('patients')->whereNull('date_registered')->update(['date_registered' => DB::raw('created_at')]);

        Schema::table('patients', function (Blueprint $table) {
            $table->unique('id_number', 'patients_id_number_unique');
        });

        Schema::table('medical_records', function (Blueprint $table) {
            $table->unsignedBigInteger('student_complaint_id')->nullable()->unique()->after('patient_id');
            $table->string('consultation_status')->default('Pending Consultation')->after('student_complaint_id');
            $table->text('symptoms_description')->nullable()->after('chief_complaint');
            $table->string('urgency_level')->nullable()->after('symptoms_description');
            $table->timestamp('submitted_at')->nullable()->after('urgency_level');
            $table->unsignedBigInteger('reviewed_by')->nullable()->after('submitted_at');
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            $table->unsignedBigInteger('attending_staff_id')->nullable()->after('reviewed_at');

            $table->foreign('student_complaint_id')->references('id')->on('student_complaints')->onDelete('set null');
            $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('attending_staff_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('medical_records', function (Blueprint $table) {
            $table->dropForeign(['student_complaint_id']);
            $table->dropForeign(['reviewed_by']);
            $table->dropForeign(['attending_staff_id']);
            $table->dropColumn([
                'student_complaint_id',
                'consultation_status',
                'symptoms_description',
                'urgency_level',
                'submitted_at',
                'reviewed_by',
                'reviewed_at',
                'attending_staff_id',
            ]);
        });

        Schema::table('patients', function (Blueprint $table) {
            $table->dropUnique('patients_id_number_unique');
            $table->dropColumn(['email', 'civil_status', 'date_registered', 'last_reviewed_at']);
        });
    }
}
