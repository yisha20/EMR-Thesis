<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class EnsurePatientSystemRole extends Migration
{
    public function up()
    {
        DB::table('roles')->updateOrInsert(['name' => 'Patient'], ['name' => 'Patient']);
    }

    public function down()
    {
        // Preserve the role when any existing account references it.
        $role = DB::table('roles')->where('name', 'Patient')->first();
        if ($role && ! DB::table('users')->where('role_id', $role->id)->exists()) {
            DB::table('roles')->where('id', $role->id)->delete();
        }
    }
}
