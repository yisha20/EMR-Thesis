<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserReportContextToSystemIncidents extends Migration
{
    public function up()
    {
        Schema::table('system_incidents', function (Blueprint $table) {
            $table->json('report_context')->nullable()->after('technical_message');
            $table->string('screenshot_path')->nullable()->after('report_context');
        });
    }

    public function down()
    {
        Schema::table('system_incidents', function (Blueprint $table) {
            $table->dropColumn(['report_context', 'screenshot_path']);
        });
    }
}
