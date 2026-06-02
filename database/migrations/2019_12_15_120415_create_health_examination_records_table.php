<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHealthExaminationRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('health_examination_records', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('patient_id');
            $table->string('past_medical_history_others')->nullable();
            $table->string('family_history_others')->nullable();
            $table->date('last_menstrual_period')->nullable();
            $table->string('menstrual_pattern')->nullable();
            $table->json('past_medical_history')->nullable();
            $table->json('family_history')->nullable();
            $table->json('social_history')->nullable();
            $table->json('phyiscal_examination')->nullable();
            $table->json('vital_signs')->nullable();
            $table->json('assessment')->nullable();
            $table->json('nursing_interventions')->nullable();
            $table->string('reccommendation')->nullable();  
            $table->date('examination_date')->nullable();
            $table->unsignedBigInteger('added_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
        });

        // Schema::table('health_examination_records', function (Blueprint $table) {
        //     $table->foreign('physical_examination_id')->references('id')->on('physical_examinations');
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('health_examination_records');
    }
}
