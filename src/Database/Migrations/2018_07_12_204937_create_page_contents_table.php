<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePageContentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('page_contents', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->timestamps();
            $table->softDeletes();
            $table->integer('page_id')->unsigned();
            $table->integer('page_content_type_id')->unsigned();
            $table->integer('position');
            $table->string('name');
            $table->string('source')->nullable();
            $table->string('note')->nullable();
            $table->longText('content')->nullable();

            $table->foreign('page_id')->references('id')->on('pages')->onDelete('cascade');
            $table->foreign('page_content_type_id')->references('id')->on('page_content_types')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('page_contents');
    }
}
