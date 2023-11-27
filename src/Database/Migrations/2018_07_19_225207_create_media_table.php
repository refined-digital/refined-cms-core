<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMediaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('media', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->softDeletes();
            $table->integer('media_category_id')->unsigned();
            $table->boolean('active')->default(1);
            $table->integer('position');
            $table->string('name');
            $table->string('mime')->nullable();
            $table->string('file');
            $table->string('alt')->nullable();
            $table->text('description')->nullable();
            $table->text('external_id')->nullable();
            $table->text('external_url')->nullable();

            $table->foreign('media_category_id')->references('id')->on('media_categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('media');
    }
}
