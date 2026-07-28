<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSoapAndPrescriptionSnapshots extends Migration
{
    public function up()
    {
        Schema::table('consultations', function (Blueprint $table) {
            $table->text('subjective')->nullable()->after('nurse_notes');
            $table->text('objective')->nullable()->after('subjective');
            $table->text('assessment')->nullable()->after('objective');
            $table->text('plan')->nullable()->after('assessment');
        });
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->string('issuing_doctor_name')->nullable();
            $table->string('issuing_doctor_specialty')->nullable();
            $table->string('issuing_doctor_prc_number')->nullable();
            $table->string('issuing_doctor_ptr_number')->nullable();
            $table->string('issuing_doctor_title')->nullable();
            $table->unsignedInteger('signature_version')->nullable();
            $table->json('template_snapshot')->nullable();
        });
    }

    public function down()
    {
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->dropColumn([
                'issuing_doctor_name', 'issuing_doctor_specialty', 'issuing_doctor_prc_number',
                'issuing_doctor_ptr_number', 'issuing_doctor_title', 'signature_version', 'template_snapshot',
            ]);
        });
        Schema::table('consultations', function (Blueprint $table) {
            $table->dropColumn(['subjective', 'objective', 'assessment', 'plan']);
        });
    }
}
