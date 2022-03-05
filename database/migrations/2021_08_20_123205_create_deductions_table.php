<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateDeductionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deductions', function (Blueprint $table) {
            $table->id();
            $table->string('description');
            $table->decimal('amount', $precision = 10, $scale = 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('deductions')->insert(
            [
                [
                    'description' => 'SSS',
                    'amount' => 100
                ],
                [
                    'description' => 'PAG IBIG',
                    'amount' => 200
                ],
                [
                    'description' => 'PHIL HEALTH',
                    'amount' => 80
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
        Schema::dropIfExists('deductions');
    }
}
