<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddQueueNotificationAndPresenceWorkflow extends Migration
{
    public function up()
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->string('notification_type')->nullable()->after('type')->index();
            $table->unsignedBigInteger('related_complaint_id')->nullable()->after('related_patient_id');
            $table->string('action_url')->nullable()->after('related_queue_id');
            $table->string('deduplication_key')->nullable()->unique()->after('action_url');
            $table->timestamp('read_at')->nullable()->after('is_read');
            $table->timestamp('delivered_at')->nullable()->after('read_at');
            $table->foreign('related_complaint_id')->references('id')->on('student_complaints')->onDelete('set null');
        });

        Schema::table('clinic_queues', function (Blueprint $table) {
            $table->enum('presence_status', ['waiting_inside','temporarily_away','returning','present'])->default('waiting_inside')->after('status')->index();
            $table->timestamp('away_at')->nullable()->after('presence_status');
            $table->timestamp('returning_at')->nullable()->after('away_at');
            $table->timestamp('present_at')->nullable()->after('returning_at');
            $table->timestamp('nearly_next_notified_at')->nullable()->after('present_at');
            $table->timestamp('next_notified_at')->nullable()->after('nearly_next_notified_at');
            $table->timestamp('called_notification_sent_at')->nullable()->after('next_notified_at');
            $table->timestamp('patient_acknowledged_at')->nullable()->after('called_notification_sent_at');
            $table->unsignedInteger('recall_count')->default(0)->after('patient_acknowledged_at');
            $table->timestamp('last_recalled_at')->nullable()->after('recall_count');
            $table->string('missed_reason')->nullable()->after('last_recalled_at');
        });
    }

    public function down()
    {
        Schema::table('clinic_queues', function (Blueprint $table) {
            $table->dropIndex(['presence_status']);
            $table->dropColumn(['presence_status','away_at','returning_at','present_at','nearly_next_notified_at',
                'next_notified_at','called_notification_sent_at','patient_acknowledged_at','recall_count',
                'last_recalled_at','missed_reason']);
        });
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropForeign(['related_complaint_id']);
            $table->dropUnique(['deduplication_key']);
            $table->dropIndex(['notification_type']);
            $table->dropColumn(['notification_type','related_complaint_id','action_url','deduplication_key',
                'read_at','delivered_at']);
        });
    }
}
