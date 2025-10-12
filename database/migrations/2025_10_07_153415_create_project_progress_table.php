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
        Schema::create('project_progress', function (Blueprint $table) {
            $table->id('project_progress_id');
            $table->unsignedBigInteger('project_id');
            $table->date('progress_date');
            $table->string('image_path')->nullable();
            $table->string('file_path')->nullable();
            $table->text('progress_description');
            $table->foreign('project_id')->references('project_id')->on('projects')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_progress');
    }
};
