<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('role_id');
            $table->foreign('role_id')->references('id')->on('roles');
            $table->string('username')->unique();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('users')->insert(
            [
                [
                    'role_id' => 1,
                    'username' => 'admin',
                    'password' => '$2a$12$wYCT3/7TSgLRZ50ESNkos.5lC8E.fg4f0f74wiaxh87BHolyPoUru'
                ],
                [
                    'role_id' => 1,
                    'username' => 'admin1',
                    'password' => '$2a$12$CDD1mjd3kuQAiGgrquM3weRE2PJHsO/dtZEL8IpImr//oont/lJT6'
                ],
                [
                    'role_id' => 1,
                    'username' => 'admin2',
                    'password' => '$2a$12$nDIIGcRHLuDKnRmy.7qT/eIQvScvZ9zYbsWTbdIX5GbQQmfYIz6vq'
                ],
                [
                    'role_id' => 1,
                    'username' => 'admin3',
                    'password' => '$2a$12$nDIIGcRHLuDKnRmy.7qT/eIQvScvZ9zYbsWTbdIX5GbQQmfYIz6vq'
                ],
                [
                    'role_id' => 2,
                    'username' => 'customer1',
                    'password' => '$2a$12$nDIIGcRHLuDKnRmy.7qT/eIQvScvZ9zYbsWTbdIX5GbQQmfYIz6vq'
                ],
                [
                    'role_id' => 2,
                    'username' => 'customer2',
                    'password' => '$2a$12$nDIIGcRHLuDKnRmy.7qT/eIQvScvZ9zYbsWTbdIX5GbQQmfYIz6vq'
                ],
                [
                    'role_id' => 2,
                    'username' => 'customer3',
                    'password' => '$2a$12$nDIIGcRHLuDKnRmy.7qT/eIQvScvZ9zYbsWTbdIX5GbQQmfYIz6vq'
                ],
                [
                    'role_id' => 2,
                    'username' => 'customer4',
                    'password' => '$2a$12$nDIIGcRHLuDKnRmy.7qT/eIQvScvZ9zYbsWTbdIX5GbQQmfYIz6vq'
                ],
                [
                    'role_id' => 2,
                    'username' => 'customer5',
                    'password' => '$2a$12$nDIIGcRHLuDKnRmy.7qT/eIQvScvZ9zYbsWTbdIX5GbQQmfYIz6vq'
                ],
                [
                    'role_id' => 2,
                    'username' => 'customer6',
                    'password' => '$2a$12$nDIIGcRHLuDKnRmy.7qT/eIQvScvZ9zYbsWTbdIX5GbQQmfYIz6vq'
                ],
                [
                    'role_id' => 2,
                    'username' => 'customer7',
                    'password' => '$2a$12$nDIIGcRHLuDKnRmy.7qT/eIQvScvZ9zYbsWTbdIX5GbQQmfYIz6vq'
                ],
                [
                    'role_id' => 2,
                    'username' => 'customer8',
                    'password' => '$2a$12$nDIIGcRHLuDKnRmy.7qT/eIQvScvZ9zYbsWTbdIX5GbQQmfYIz6vq'
                ],
                [
                    'role_id' => 2,
                    'username' => 'customer9',
                    'password' => '$2a$12$nDIIGcRHLuDKnRmy.7qT/eIQvScvZ9zYbsWTbdIX5GbQQmfYIz6vq'
                ],
                [
                    'role_id' => 2,
                    'username' => 'customer10',
                    'password' => '$2a$12$nDIIGcRHLuDKnRmy.7qT/eIQvScvZ9zYbsWTbdIX5GbQQmfYIz6vq'
                ],
                [
                    'role_id' => 2,
                    'username' => 'customer11',
                    'password' => '$2a$12$nDIIGcRHLuDKnRmy.7qT/eIQvScvZ9zYbsWTbdIX5GbQQmfYIz6vq'
                ],
                [
                    'role_id' => 2,
                    'username' => 'customer12',
                    'password' => '$2a$12$nDIIGcRHLuDKnRmy.7qT/eIQvScvZ9zYbsWTbdIX5GbQQmfYIz6vq'
                ],
                [
                    'role_id' => 2,
                    'username' => 'customer13',
                    'password' => '$2a$12$nDIIGcRHLuDKnRmy.7qT/eIQvScvZ9zYbsWTbdIX5GbQQmfYIz6vq'
                ],
                [
                    'role_id' => 2,
                    'username' => 'customer14',
                    'password' => '$2a$12$nDIIGcRHLuDKnRmy.7qT/eIQvScvZ9zYbsWTbdIX5GbQQmfYIz6vq'
                ],
                [
                    'role_id' => 2,
                    'username' => 'customer15',
                    'password' => '$2a$12$nDIIGcRHLuDKnRmy.7qT/eIQvScvZ9zYbsWTbdIX5GbQQmfYIz6vq'
                ],
                [
                    'role_id' => 2,
                    'username' => 'customer16',
                    'password' => '$2a$12$nDIIGcRHLuDKnRmy.7qT/eIQvScvZ9zYbsWTbdIX5GbQQmfYIz6vq'
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
        Schema::dropIfExists('users');
    }
}
