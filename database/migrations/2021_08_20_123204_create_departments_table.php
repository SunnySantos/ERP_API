<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateDepartmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('departments')->insert(
            [
                [
                    'name' => 'Administrator'
                ],
                [
                    'name' => 'Customer Relationship'
                ],
                [
                    'name' => 'Human Resource'
                ],
                [
                    'name' => 'Finance'
                ],
                [
                    'name' => 'Supply Chain'
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
        Schema::dropIfExists('departments');
    }
}