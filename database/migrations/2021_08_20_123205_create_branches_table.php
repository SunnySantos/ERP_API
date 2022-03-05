<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateBranchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('address');
            $table->string('phone_number');
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('branches')->insert(
            [
                [
                    'name' => 'Main Branch',
                    'address' => 'Parklane, Dasmariñas City, Cavite, Philippines',
                    'phone_number' => '9647831234'
                ],
                [
                    'name' => 'Branch A',
                    'address' => 'Green Park, Imus City, Cavite, Philippines',
                    'phone_number' => '9647831232'
                ],
                [
                    'name' => 'Branch B',
                    'address' => 'Mary Cris, General Trias City, Cavite, Philippines',
                    'phone_number' => '9647831233'
                ]
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
        Schema::dropIfExists('branches');
    }
}
