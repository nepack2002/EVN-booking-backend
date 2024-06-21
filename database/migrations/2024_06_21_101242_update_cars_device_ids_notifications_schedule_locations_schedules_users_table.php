<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        //
        Schema::table('cars', function (Blueprint $table) {
            $table->dropForeign('cars_user_id_foreign');
        });
        Schema::table('device_ids', function (Blueprint $table) {
            $table->dropForeign('device_ids_user_id_foreign');
        });
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropForeign('notifications_user_id_foreign');
        });
        Schema::table('schedule_locations', function (Blueprint $table) {
            $table->dropForeign('schedule_locations_schedule_id_foreign');
        });
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropForeign('schedules_car_id_foreign');
            $table->dropForeign('schedules_department_id_foreign');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign('users_department_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
