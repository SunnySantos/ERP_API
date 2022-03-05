<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCakeComponentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cake_components', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cake_model_id');
            $table->foreign('cake_model_id')->references('id')->on('cake_models')->onDelete('cascade');
            $table->string('name');
            $table->string('size')->nullable();
            $table->string('category');
            $table->string('shape')->nullable();
            $table->decimal('cost', $precision = 8, $scale = 2);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cake_components');
    }
}
