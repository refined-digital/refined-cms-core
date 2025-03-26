<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('contents', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('position')->default(0);
            $table->string('contentable_id');
            $table->string('contentable_type');
            $table->string('content_class');
            $table->string('field');
            $table->json('data')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contents');
    }
};
