<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSystemMonitoringTables extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('system_incidents')) {
            Schema::create('system_incidents', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('reference_code')->unique();
                $table->string('severity', 20)->default('medium')->index();
                $table->string('category', 40)->index();
                $table->string('event_type', 100);
                $table->string('deduplication_key')->nullable()->index();
                $table->string('status', 30)->default('open')->index();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('user_role', 50)->nullable();
                $table->string('resource_type', 80)->nullable();
                $table->unsignedBigInteger('resource_id')->nullable();
                $table->string('route_name')->nullable();
                $table->string('request_method', 10)->nullable();
                $table->unsignedSmallInteger('http_status')->nullable();
                $table->text('safe_message');
                $table->text('technical_message')->nullable();
                $table->timestamp('detected_at')->index();
                $table->timestamp('resolved_at')->nullable();
                $table->unsignedBigInteger('resolved_by')->nullable();
                $table->text('resolution_notes')->nullable();
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
                $table->foreign('resolved_by')->references('id')->on('users')->onDelete('set null');
            });
        }

        if (! Schema::hasTable('workflow_action_logs')) {
            Schema::create('workflow_action_logs', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('action_name', 100)->index();
                $table->string('result', 20)->index();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('user_role', 50)->nullable();
                $table->string('resource_type', 80)->nullable();
                $table->unsignedBigInteger('resource_id')->nullable();
                $table->string('route_name')->nullable();
                $table->unsignedSmallInteger('http_status')->nullable();
                $table->string('error_reference')->nullable()->index();
                $table->unsignedInteger('duration_ms')->nullable();
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('workflow_action_logs');
        Schema::dropIfExists('system_incidents');
    }
}
