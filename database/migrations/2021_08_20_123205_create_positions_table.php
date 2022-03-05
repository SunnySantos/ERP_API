<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreatePositionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->decimal('rate', $precision = 10, $scale = 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('positions')->insert(
            [
                [
                    'title' => 'CEO',
                    'rate' => 10000
                ],
                [
                    'title' => 'Branch Manager',
                    'rate' => 105
                ],
                [
                    'title' => 'Customer Relationship Manager',
                    'rate' => 129
                ],
                [
                    'title' => 'Recruiting Manager',
                    'rate' => 201
                ],
                [
                    'title' => 'Inventory Associate',
                    'rate' => 100
                ],
                [
                    'title' => 'Financial Manager',
                    'rate' => 116
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
        Schema::dropIfExists('positions');
    }
}
