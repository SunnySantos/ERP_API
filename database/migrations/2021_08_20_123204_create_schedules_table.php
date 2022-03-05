<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->time('time_in');
            $table->time('time_out');
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('schedules')->insert(
            [
                
                [
                    'time_in' => '07:00',
                    'time_out' => '16:00'
                ],
                [
                    'time_in' => '08:00',
                    'time_out' => '17:00'
                ],
                [
                    'time_in' => '09:00',
                    'time_out' => '18:00'
                ],
                [
                    'time_in' => '10:00',
                    'time_out' => '19:00'
                ],
            ]
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('schedules');
    }
}
