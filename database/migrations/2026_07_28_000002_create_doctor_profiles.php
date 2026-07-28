<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateDoctorProfiles extends Migration
{
    public function up()
    {
        Schema::create('doctor_profiles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->unique();
            $table->string('availability')->default('available')->index();
            $table->string('specialty')->nullable();
            $table->string('professional_title')->nullable();
            $table->string('clinic_designation')->nullable();
            $table->string('prc_number')->nullable();
            $table->string('ptr_number')->nullable();
            $table->string('s2_number')->nullable();
            $table->string('contact_number')->nullable();
            $table->text('clinic_address')->nullable();
            $table->string('professional_logo_path')->nullable();
            $table->string('signature_path')->nullable();
            $table->string('signature_status')->default('not_uploaded');
            $table->unsignedInteger('signature_version')->nullable();
            $table->timestamp('signature_uploaded_at')->nullable();
            $table->timestamp('signature_verified_at')->nullable();
            $table->unsignedBigInteger('signature_verified_by')->nullable();
            $table->text('prescription_footer')->nullable();
            $table->json('template_settings')->nullable();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('signature_verified_by')->references('id')->on('users')->onDelete('set null');
        });

        $now = now();
        $doctors = DB::table('users')->join('roles', 'roles.id', '=', 'users.role_id')
            ->where('roles.name', 'Doctor')->select('users.id', 'users.license_number')->get();
        foreach ($doctors as $doctor) {
            DB::table('doctor_profiles')->insert([
                'user_id' => $doctor->id, 'availability' => 'available',
                'prc_number' => $doctor->license_number, 'signature_status' => 'not_uploaded',
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }
    }

    public function down()
    {
        Schema::dropIfExists('doctor_profiles');
    }
}
