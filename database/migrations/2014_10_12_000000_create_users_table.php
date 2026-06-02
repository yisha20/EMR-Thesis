<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('role_id');
            $table->string('email')->unique();
            $table->string('avatar')->nullable();
            $table->string('password');
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('gender')->nullable();
            $table->string('civil_status')->nullable();
            $table->integer('age')->nullable();
            $table->string('birthdate')->nullable();
            $table->string('present_address')->nullable();
            $table->string('home_address')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('license_number')->nullable();
            $table->rememberToken();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamp('last_activity')->nullable();
            $table->timestamps();
            // $table->foreign('role_id')->references('id')->on('roles');
        });

        // Schema::table('users', function (Blueprint $table) {
        //     $table->foreign('role_id')->references('id')->on('roles');
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
