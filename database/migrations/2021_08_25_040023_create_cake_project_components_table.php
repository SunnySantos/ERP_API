<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCakeProjectComponentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cake_project_components', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cake_project_id');
            $table->foreign('cake_project_id')->references('id')->on('cake_projects')->onDelete('cascade');
            $table->unsignedBigInteger('cake_component_id');
            $table->foreign('cake_component_id')->references('id')->on('cake_components')->onDelete('cascade');
            $table->longText('uuid');
            $table->decimal('posX', 8, 4);
            $table->decimal('posY', 8, 4);
            $table->decimal('posZ', 8, 4);
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
        Schema::dropIfExists('cake_project_components');
    }
}
