<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ConsolidateClinicQueueWorkflow extends Migration
{
    public function up()
    {
        Schema::create('clinic_queue_sequences', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('queue_date');
            $table->enum('queue_type', ['counter', 'consultation']);
            $table->unsignedInteger('last_sequence')->default(0);
            $table->timestamps();
            $table->unique(['queue_date', 'queue_type']);
        });

        Schema::create('clinic_queue_dispatch_states', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('queue_date')->unique();
            $table->enum('policy', ['alternating', 'strict_priority', 'manual'])->default('alternating');
            $table->enum('last_dispatched_type', ['counter', 'consultation'])->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });

        Schema::table('clinic_queues', function (Blueprint $table) {
            $table->unsignedBigInteger('assigned_nurse_id')->nullable()->after('assigned_staff_id');
            $table->unsignedBigInteger('assigned_doctor_id')->nullable()->after('assigned_nurse_id');
            $table->timestamp('missed_at')->nullable()->after('completed_at');
            $table->unsignedBigInteger('transferred_from_queue_id')->nullable()->after('missed_at');
            $table->foreign('assigned_nurse_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('assigned_doctor_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('transferred_from_queue_id')->references('id')->on('clinic_queues')->onDelete('set null');
            $table->index(['student_complaint_id', 'queue_type', 'status'], 'queue_active_complaint_route_idx');
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::table('clinic_queues', function (Blueprint $table) {
            $table->dropForeign(['assigned_nurse_id']);
            $table->dropForeign(['assigned_doctor_id']);
            $table->dropForeign(['transferred_from_queue_id']);
            $table->dropIndex('queue_active_complaint_route_idx');
            $table->dropIndex(['created_at']);
            $table->dropColumn(['assigned_nurse_id', 'assigned_doctor_id', 'missed_at', 'transferred_from_queue_id']);
        });
        Schema::dropIfExists('clinic_queue_dispatch_states');
        Schema::dropIfExists('clinic_queue_sequences');
    }
}
