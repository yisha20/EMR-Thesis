<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AlignPatientRegistrationFields extends Migration
{
    public function up()
    {
        Schema::table('patient_accounts', function (Blueprint $table) {
            $table->string('suffix', 20)->nullable();
            $table->string('college_department')->nullable();
            $table->string('program')->nullable();
            $table->string('year_level', 50)->nullable();
            $table->string('position_designation')->nullable();
            $table->string('employment_type', 100)->nullable();
            $table->string('sponsor_type', 20)->nullable();
            $table->string('sponsor_id_number', 50)->nullable();
            $table->string('sponsor_email')->nullable();
            $table->boolean('verification_consent')->default(false);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('temporary_password_expires_at')->nullable()->index();
        });

        // Safe compatibility backfill: only copy confirmed student data.
        DB::table('patient_accounts')
            ->join('students', 'students.user_id', '=', 'patient_accounts.user_id')
            ->where('patient_accounts.patient_type', 'student')
            ->whereNull('patient_accounts.college_department')
            ->update(['patient_accounts.college_department' => DB::raw('students.college_department')]);
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['temporary_password_expires_at']);
            $table->dropColumn('temporary_password_expires_at');
        });
        Schema::table('patient_accounts', function (Blueprint $table) {
            $table->dropColumn([
                'suffix', 'college_department', 'program', 'year_level',
                'position_designation', 'employment_type', 'sponsor_type',
                'sponsor_id_number', 'sponsor_email', 'verification_consent',
            ]);
        });
    }
}
