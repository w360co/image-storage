<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateImageStoragesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(){

        Schema::create('image_storages', function (Blueprint $table) {
            $table->unsignedBigInteger('id',true);
            $table->string('name')->index();
            $table->string('storage')->index();
            $table->string('author')->nullable();
            $table->string('model_type')->nullable();
            $table->unsignedBigInteger('model_id')->nullable();
            $table->index(['model_id', 'model_type'], 'model_has_images_model_id_model_type_index');
            $table->timestamps();
            $table->index('created_at');
            $table->index('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('image_storages');
    }
}