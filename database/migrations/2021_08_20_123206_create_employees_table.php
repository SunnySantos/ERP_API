<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // REMOVE NULLABLE METHOD ON PASSWORD COLUMN
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('department_id');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('branch_id');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            $table->unsignedBigInteger('position_id');
            $table->foreign('position_id')->references('id')->on('positions')->onDelete('cascade');
            $table->string('firstname');
            $table->string('middlename')->nullable();
            $table->string('lastname');
            $table->string('address');
            $table->string('sex')->default('Male');
            $table->string('marital_status')->default('Single');
            $table->date('birth')->default('2012/12/12');
            $table->string('phone_number');
            $table->date('hire')->default(date('Y-m-d'));
            $table->string('photo')->default('default_m.jpg');
            $table->timestamps();
            $table->softDeletes();
        });


        DB::table('employees')->insert(
            [
                [
                    'department_id' => 1,
                    'user_id' => 1,
                    'branch_id' => 1,
                    'firstname' => 'John Mark',
                    'lastname' => 'Vasquez',
                    'address' => '',
                    'phone_number' => '9123456789',
                    'position_id' => 1
                ],
                [
                    'department_id' => 2,
                    'user_id' => 2,
                    'branch_id' => 1,
                    'firstname' => 'John Freud',
                    'lastname' => 'Toledo',
                    'address' => '',
                    'phone_number' => '9123456788',
                    'position_id' => 2,
                ],
                [
                    'department_id' => 3,
                    'user_id' => 3,
                    'branch_id' => 3,
                    'firstname' => 'Jonathan',
                    'lastname' => 'Dulay',
                    'address' => '',
                    'phone_number' => '9123456787',
                    'position_id' => 3,
                ],
                [
                    'department_id' => 5,
                    'user_id' => 4,
                    'branch_id' => 3,
                    'firstname' => 'Renz',
                    'lastname' => 'Racelis',
                    'address' => '',
                    'phone_number' => '9123456786',
                    'position_id' => 3,
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
        Schema::dropIfExists('employees');
    }
}
