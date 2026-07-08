<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddComplaintCategoryToStudentComplaints extends Migration
{
    public function up()
    {
        Schema::table('student_complaints', function (Blueprint $table) {
            $table->string('complaint_category')->nullable()->after('student_name');
        });
    }

    public function down()
    {
        Schema::table('student_complaints', function (Blueprint $table) {
            $table->dropColumn('complaint_category');
        });
    }
}
