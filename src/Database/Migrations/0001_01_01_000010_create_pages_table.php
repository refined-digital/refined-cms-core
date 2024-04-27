<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->timestamps();
            $table->softDeletes();
            $table->integer('page_holder_id')->unsigned();
            $table->integer('parent_id');
            $table->boolean('active')->default(1);
            $table->boolean('hide_from_menu')->default(0);
            $table->boolean('protected')->default(0);
            $table->boolean('page_type')->default(1);
            $table->integer('form_id')->nullable();
            $table->integer('position');
            $table->string('name');
            $table->integer('banner')->nullable();
            $table->json('data')->nullable();
            $table->json('content')->nullable();

            $table->foreign('page_holder_id')->references('id')->on('page_holders')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pages');
    }
}
