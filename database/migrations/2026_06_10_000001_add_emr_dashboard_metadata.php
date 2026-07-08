<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddEmrDashboardMetadata extends Migration
{
    public function up()
    {
        Schema::table('services', function (Blueprint $table) {
            $table->string('category')->default('Consultation')->after('name');
            $table->string('status')->default('Active')->after('description');
            $table->unsignedBigInteger('archived_by')->nullable()->after('added_by');
        });

        Schema::table('patients', function (Blueprint $table) {
            $table->unsignedBigInteger('archived_by')->nullable()->after('updated_by');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('last_login_at')->nullable()->after('last_activity');
            $table->unsignedBigInteger('archived_by')->nullable()->after('last_login_at');
        });

        DB::table('services')
            ->whereRaw('LOWER(TRIM(description)) = ?', ['some description'])
            ->update(['description' => 'Clinical service details are available from clinic staff.']);
    }

    public function down()
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['category', 'status', 'archived_by']);
        });

        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn('archived_by');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['last_login_at', 'archived_by']);
        });
    }
}
