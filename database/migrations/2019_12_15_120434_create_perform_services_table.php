<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePerformServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('perform_services', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('health_examination_record_id');
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('user_id');  
            $table->string('name');
            $table->date('service_date');
            $table->string('remarks');
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
        });

        // Schema::table('perform_services', function (Blueprint $table) {
        //     $table->foreign('health_examination_record_id')->references('id')->on('health_examination_records');
        //     $table->foreign('patient_id')->references('id')->on('patients');
        //     $table->foreign('user_id')->references('id')->on('users');
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('perform_services');
    }
}
