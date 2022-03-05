<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('firstname');
            $table->string('middlename')->nullable();
            $table->string('lastname');
            $table->string('address');
            $table->string('phone_number');
            $table->string('profile')->default('default.jpg');
            $table->string('email');
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('customers')->insert(
            [
                [
                    'user_id' => 5,
                    'firstname' => 'Faustino',
                    'lastname' => 'Basilides',
                    'address' => 'Parklane, Dasmariñas City, Cavite',
                    'phone_number' => '9242653476',
                    'email' => 'basilides@gmail.com'
                ],
                [
                    'user_id' => 6,
                    'firstname' => 'Aether',
                    'lastname' => 'Vega',
                    'address' => 'Habay 1, Bacoor, Cavite',
                    'phone_number' => '9065428432',
                    'email' => 'vegaae@gmail.com'
                ],
                [
                    'user_id' => 7,
                    'firstname' => 'Lumine',
                    'lastname' => 'Cuenca',
                    'address' => 'Molino, Bacoor, Cavite',
                    'phone_number' => '9354658542',
                    'email' => 'lumilight@gmail.com'
                ],
                [
                    'user_id' => 8,
                    'firstname' => 'Morgan',
                    'lastname' => 'Tejada',
                    'address' => 'Anabu 2, Imus, Cavite',
                    'phone_number' => '90654215215',
                    'email' => 'morggy@gmail.com'
                ],
                [
                    'user_id' => 9,
                    'firstname' => 'Mike',
                    'lastname' => 'Abad',
                    'address' => 'Gahak, Kawit, Cavite',
                    'phone_number' => '90665865854',
                    'email' => 'abadmike@gmail.com'
                ],
                [
                    'user_id' => 10,
                    'firstname' => 'Yohan',
                    'lastname' => 'Trajico',
                    'address' => 'Wakas, Kawit, Cavite',
                    'phone_number' => '9068828854',
                    'email' => 'yohamtraj@gmail.com'

                ],
                [
                    'user_id' => 11,
                    'firstname' => 'Tracy',
                    'lastname' => 'Barcelona',
                    'address' => 'Habay 1, Bacoor, Cavite',
                    'phone_number' => '9125456454',
                    'email' => 'tracyb@gmail.com'
                ],
                [
                    'user_id' => 12,
                    'firstname' => 'Jena',
                    'lastname' => 'Cruz',
                    'address' => 'Maitim, Silang, Cavite',
                    'phone_number' => '9051245354',
                    'email' => 'jenaaaz@gmail.com'
                ],
                [
                    'user_id' => 13,
                    'firstname' => 'Eleanor',
                    'lastname' => 'Astillo',
                    'address' => 'Halayhay, Tanza, Cavite',
                    'phone_number' => '9051325932',
                    'email' => 'ellya@gmail.com'
                ],
                [
                    'user_id' => 14,
                    'firstname' => 'Yasmin',
                    'lastname' => 'Hagan',
                    'address' => 'Bucandala, Imus, Cavite',
                    'phone_number' => '9154864235',
                    'email' => 'haganyasmin@gmail.com'
                ],
                [
                    'user_id' => 15,
                    'firstname' => 'Darryl',
                    'lastname' => 'Samonte',
                    'address' => 'Habay 1, Bacoor, Cavite',
                    'phone_number' => '955124345',
                    'email' => 'kamoteboy@gmail.com'
                ],
                [
                    'user_id' => 16,
                    'firstname' => 'Catarina',
                    'lastname' => 'Claes',
                    'address' => 'Tabon, Kawit, Cavite',
                    'phone_number' => '9064515342',
                    'email' => 'bakarina@gmail.com'
                ],
                [
                    'user_id' => 17,
                    'firstname' => 'Sophia',
                    'lastname' => 'Montenergo',
                    'address' => 'Bagtas, Tanza, Cavite',
                    'phone_number' => '9065462356',
                    'email' => 'rubywhite@gmail.com'
                ],
                [
                    'user_id' => 18,
                    'firstname' => 'Maria',
                    'lastname' => 'Campbell',
                    'address' => 'Santulan, Naic, Cavite',
                    'phone_number' => '9124542525',
                    'email' => 'mariacamps@gmail.com'
                ],
                [
                    'user_id' => 19,
                    'firstname' => 'Geordo',
                    'lastname' => 'Stuart',
                    'address' => 'Calubcob, Naic, Cavite',
                    'phone_number' => '9066535428',
                    'email' => 'doubleface@gmail.com'
                ],
                [
                    'user_id' => 20,
                    'firstname' => 'Nicol',
                    'lastname' => 'Ascart',
                    'address' => 'Malagasang, Imus, Cavite',
                    'phone_number' => '9551425524',
                    'email' => 'nicolo@gmail.com'
                ],
            ],

        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customers');
    }
}
