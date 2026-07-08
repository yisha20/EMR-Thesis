<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNurseConsultationNotifications extends Migration
{
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('role_target')->nullable();
            $table->string('title');
            $table->text('message');
            $table->string('type');
            $table->unsignedBigInteger('related_consultation_id')->nullable();
            $table->unsignedBigInteger('related_patient_id')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('related_consultation_id')->references('id')->on('consultations')->onDelete('set null');
            $table->foreign('related_patient_id')->references('id')->on('patients')->onDelete('set null');
            $table->index(['user_id', 'is_read']);
            $table->index(['role_target', 'is_read']);
        });

        Schema::table('consultations', function (Blueprint $table) {
            $table->timestamp('called_at')->nullable()->after('forwarded_at');
            $table->unsignedBigInteger('called_by')->nullable()->after('called_at');
            $table->foreign('called_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('consultations', function (Blueprint $table) {
            $table->dropForeign(['called_by']);
            $table->dropColumn(['called_at', 'called_by']);
        });

        Schema::dropIfExists('notifications');
    }
}
