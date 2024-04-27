<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUriTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('uri', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('uri');
            $table->string('name')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('template_id');
            $table->integer('uriable_id');
            $table->string('uriable_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('uri');
    }
}
