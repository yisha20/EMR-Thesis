<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ExpandPatientPortalFoundation extends Migration
{
    public function up()
    {
        Schema::create('patient_accounts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->nullable()->unique();
            $table->unsignedBigInteger('patient_id')->nullable()->unique();
            $table->enum('patient_type', ['student', 'faculty', 'dependent'])->index();
            $table->string('student_id_number')->nullable()->unique();
            $table->string('faculty_id_number')->nullable()->unique();
            $table->unsignedBigInteger('sponsor_patient_account_id')->nullable();
            $table->string('dependent_relationship')->nullable();
            $table->string('dependent_relationship_details')->nullable();
            $table->enum('verification_status', ['pending_verification', 'verified', 'rejected', 'inactive'])->default('pending_verification')->index();
            $table->enum('health_assessment_status', ['not_started', 'draft', 'patient_submitted', 'under_review', 'clinically_completed'])->default('not_started')->index();
            $table->timestamp('health_assessment_completed_at')->nullable();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('set null');
            $table->foreign('sponsor_patient_account_id')->references('id')->on('patient_accounts')->onDelete('set null');
        });

        Schema::create('patient_dependents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('sponsor_patient_account_id');
            $table->unsignedBigInteger('patient_account_id')->nullable()->unique();
            $table->string('full_name');
            $table->string('relationship');
            $table->string('relationship_details')->nullable();
            $table->date('birth_date');
            $table->string('sex', 30);
            $table->string('civil_status', 50)->nullable();
            $table->text('home_address');
            $table->string('contact_number', 50)->nullable();
            $table->string('emergency_contact');
            $table->string('proof_path')->nullable();
            $table->enum('verification_status', ['pending_verification', 'verified', 'rejected', 'inactive'])->default('pending_verification')->index();
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            $table->foreign('sponsor_patient_account_id')->references('id')->on('patient_accounts')->onDelete('cascade');
            $table->foreign('patient_account_id')->references('id')->on('patient_accounts')->onDelete('set null');
            $table->foreign('verified_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['sponsor_patient_account_id', 'full_name']);
        });

        Schema::create('health_assessments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('patient_account_id');
            $table->unsignedBigInteger('patient_id')->nullable();
            $table->enum('patient_type', ['student', 'faculty', 'dependent']);
            $table->enum('status', ['not_started', 'draft', 'patient_submitted', 'under_review', 'clinically_completed'])->default('not_started')->index();
            $table->unsignedInteger('version')->default(1);
            $table->json('personal_information')->nullable();
            $table->json('womens_health')->nullable();
            $table->json('social_history')->nullable();
            $table->json('physical_examination')->nullable();
            $table->json('vital_signs')->nullable();
            $table->json('clinical_assessment')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->unsignedBigInteger('clinically_completed_by')->nullable();
            $table->timestamp('clinically_completed_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
            $table->foreign('patient_account_id')->references('id')->on('patient_accounts')->onDelete('cascade');
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('set null');
            $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('clinically_completed_by')->references('id')->on('users')->onDelete('set null');
            $table->unique(['patient_account_id', 'version']);
        });

        Schema::create('health_assessment_medical_histories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('health_assessment_id');
            $table->string('condition');
            $table->date('diagnosis_date')->nullable();
            $table->string('current_status')->nullable();
            $table->string('medication')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->foreign('health_assessment_id', 'ha_medical_assessment_fk')->references('id')->on('health_assessments')->onDelete('cascade');
            $table->unique(['health_assessment_id', 'condition'], 'ha_medical_condition_unique');
        });

        Schema::create('health_assessment_family_histories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('health_assessment_id');
            $table->string('condition');
            $table->string('relationship')->nullable();
            $table->text('details')->nullable();
            $table->timestamps();
            $table->foreign('health_assessment_id', 'ha_family_assessment_fk')->references('id')->on('health_assessments')->onDelete('cascade');
            $table->unique(['health_assessment_id', 'condition'], 'ha_family_condition_unique');
        });

        Schema::create('health_assessment_medications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('health_assessment_id');
            $table->string('medication');
            $table->unsignedInteger('display_order')->default(0);
            $table->timestamps();
            $table->foreign('health_assessment_id')->references('id')->on('health_assessments')->onDelete('cascade');
        });

        Schema::create('health_assessment_nursing_interventions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('health_assessment_id');
            $table->text('intervention');
            $table->date('intervention_date');
            $table->time('intervention_time')->nullable();
            $table->unsignedBigInteger('performed_by');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->foreign('health_assessment_id', 'ha_nursing_assessment_fk')->references('id')->on('health_assessments')->onDelete('cascade');
            $table->foreign('performed_by')->references('id')->on('users')->onDelete('restrict');
        });

        Schema::create('common_complaint_options', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->unique();
            $table->string('category')->index();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('display_order')->default(0);
            $table->boolean('allows_multiple')->default(true);
            $table->boolean('requires_details')->default(false);
            $table->timestamps();
        });

        Schema::create('complaint_option_selections', function (Blueprint $table) {
            $table->unsignedBigInteger('student_complaint_id');
            $table->unsignedBigInteger('common_complaint_option_id');
            $table->primary(['student_complaint_id', 'common_complaint_option_id'], 'complaint_option_selection_pk');
            $table->foreign('student_complaint_id')->references('id')->on('student_complaints')->onDelete('cascade');
            $table->foreign('common_complaint_option_id')->references('id')->on('common_complaint_options')->onDelete('restrict');
        });

        Schema::table('student_complaints', function (Blueprint $table) {
            $table->unsignedBigInteger('patient_account_id')->nullable()->after('student_id');
            $table->unsignedBigInteger('dependent_id')->nullable()->after('patient_account_id');
            $table->text('other_complaint')->nullable()->after('chief_complaint');
            $table->string('triage_priority')->default('unassigned')->after('urgency_level')->index();
            $table->foreign('patient_account_id')->references('id')->on('patient_accounts')->onDelete('set null');
            $table->foreign('dependent_id')->references('id')->on('patient_dependents')->onDelete('set null');
        });

        Schema::create('clinic_queues', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('queue_date')->index();
            $table->enum('queue_type', ['counter', 'consultation'])->index();
            $table->string('ticket_number');
            $table->unsignedBigInteger('patient_account_id');
            $table->unsignedBigInteger('student_complaint_id');
            $table->unsignedBigInteger('consultation_id')->nullable();
            $table->enum('priority', ['unassigned', 'low', 'moderate', 'high', 'urgent'])->default('unassigned')->index();
            $table->enum('status', ['waiting', 'called', 'serving', 'completed', 'cancelled', 'missed', 'transferred'])->default('waiting')->index();
            $table->unsignedInteger('position')->nullable();
            $table->timestamp('called_at')->nullable();
            $table->timestamp('serving_started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedBigInteger('assigned_staff_id')->nullable();
            $table->timestamps();
            $table->foreign('patient_account_id')->references('id')->on('patient_accounts')->onDelete('cascade');
            $table->foreign('student_complaint_id')->references('id')->on('student_complaints')->onDelete('cascade');
            $table->foreign('consultation_id')->references('id')->on('consultations')->onDelete('set null');
            $table->foreign('assigned_staff_id')->references('id')->on('users')->onDelete('set null');
            $table->unique(['queue_date', 'queue_type', 'ticket_number']);
            $table->index(['queue_date', 'queue_type', 'status', 'priority'], 'clinic_queue_order_idx');
        });

        Schema::create('queue_status_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('clinic_queue_id');
            $table->unsignedBigInteger('changed_by')->nullable();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->text('reason')->nullable();
            $table->timestamps();
            $table->foreign('clinic_queue_id')->references('id')->on('clinic_queues')->onDelete('cascade');
            $table->foreign('changed_by')->references('id')->on('users')->onDelete('set null');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->unsignedBigInteger('related_queue_id')->nullable()->after('related_consultation_id');
            $table->foreign('related_queue_id')->references('id')->on('clinic_queues')->onDelete('set null');
        });

        DB::table('students')->orderBy('id')->get()->each(function ($student) {
            $patientId = DB::table('patients')->where('id_number', $student->student_id_number)->value('id');
            DB::table('patient_accounts')->insert([
                'user_id' => $student->user_id,
                'patient_id' => $patientId,
                'patient_type' => 'student',
                'student_id_number' => $student->student_id_number,
                'verification_status' => 'verified',
                // Existing portal users are grandfathered so the established intake
                // workflow remains available; newly registered users start incomplete.
                'health_assessment_status' => 'patient_submitted',
                'health_assessment_completed_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        $options = [
            ['Headache', 'General symptoms'], ['Fever', 'General symptoms'], ['Dizziness', 'General symptoms'],
            ['Cough', 'Respiratory'], ['Colds', 'Respiratory'], ['Sore throat', 'Respiratory'],
            ['Difficulty breathing', 'Respiratory'], ['Chest discomfort', 'Respiratory'],
            ['Stomach pain', 'Digestive'], ['Vomiting', 'Digestive'], ['Diarrhea', 'Digestive'], ['Constipation', 'Digestive'],
            ['Menstrual cramps', 'Women’s health'], ['Toothache', 'Dental'], ['Back pain', 'Musculoskeletal'],
            ['Joint pain', 'Musculoskeletal'], ['Muscle pain', 'Musculoskeletal'], ['Minor wound', 'Minor injury'],
            ['Allergy symptoms', 'General symptoms'], ['Skin irritation', 'Skin'], ['Medication request', 'Medication request'],
            ['Warm compress request', 'General symptoms'], ['Blood pressure check', 'General symptoms'], ['Other', 'Other'],
        ];
        foreach ($options as $order => $option) {
            DB::table('common_complaint_options')->updateOrInsert(['name' => $option[0]], [
                'category' => $option[1], 'is_active' => true, 'display_order' => $order,
                'allows_multiple' => true, 'requires_details' => $option[0] === 'Other',
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    public function down()
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropForeign(['related_queue_id']);
            $table->dropColumn('related_queue_id');
        });
        Schema::dropIfExists('queue_status_logs');
        Schema::dropIfExists('clinic_queues');
        Schema::table('student_complaints', function (Blueprint $table) {
            $table->dropForeign(['patient_account_id']);
            $table->dropForeign(['dependent_id']);
            $table->dropColumn(['patient_account_id', 'dependent_id', 'other_complaint', 'triage_priority']);
        });
        Schema::dropIfExists('complaint_option_selections');
        Schema::dropIfExists('common_complaint_options');
        Schema::dropIfExists('health_assessment_nursing_interventions');
        Schema::dropIfExists('health_assessment_medications');
        Schema::dropIfExists('health_assessment_family_histories');
        Schema::dropIfExists('health_assessment_medical_histories');
        Schema::dropIfExists('health_assessments');
        Schema::dropIfExists('patient_dependents');
        Schema::dropIfExists('patient_accounts');
    }
}
