<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CompleteStudentRegistrationProfiles extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('name')->nullable()->after('username');
            $table->string('status')->default('Active')->after('name');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->string('first_name')->nullable()->after('student_id_number');
            $table->string('middle_name')->nullable()->after('first_name');
            $table->string('last_name')->nullable()->after('middle_name');
            $table->string('email')->nullable()->after('last_name');
            $table->string('gender')->nullable()->after('contact_number');
            $table->date('birth_date')->nullable()->after('gender');
            $table->unsignedInteger('age')->nullable()->after('birth_date');
            $table->string('civil_status')->nullable()->after('age');
            $table->string('home_address')->nullable()->after('civil_status');
            $table->string('present_address')->nullable()->after('home_address');
        });

        DB::table('users')->whereNull('name')->update([
            'name' => DB::raw("TRIM(CONCAT(first_name, ' ', COALESCE(middle_name, ''), ' ', last_name))"),
        ]);

        DB::table('students')
            ->join('users', 'users.id', '=', 'students.user_id')
            ->update([
                'students.first_name' => DB::raw('users.first_name'),
                'students.middle_name' => DB::raw('users.middle_name'),
                'students.last_name' => DB::raw('users.last_name'),
                'students.email' => DB::raw('users.email'),
                'students.gender' => DB::raw('users.gender'),
                'students.birth_date' => DB::raw('users.birthdate'),
                'students.age' => DB::raw('users.age'),
                'students.civil_status' => DB::raw('users.civil_status'),
                'students.home_address' => DB::raw('users.home_address'),
                'students.present_address' => DB::raw('users.present_address'),
            ]);
    }

    public function down()
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn([
                'first_name',
                'middle_name',
                'last_name',
                'email',
                'gender',
                'birth_date',
                'age',
                'civil_status',
                'home_address',
                'present_address',
            ]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['name', 'status']);
        });
    }
}
