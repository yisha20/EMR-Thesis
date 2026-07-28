<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EnhanceClinicNotifications extends Migration
{
    public function up()
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->string('priority')->default('routine')->after('deduplication_key')->index();
            $table->timestamp('display_until')->nullable()->after('priority');
            $table->timestamp('acknowledged_at')->nullable()->after('display_until');
        });
    }

    public function down()
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex(['priority']);
            $table->dropColumn(['priority', 'display_until', 'acknowledged_at']);
        });
    }
}
