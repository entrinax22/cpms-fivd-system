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
        Schema::create('requested_tools', function (Blueprint $table) {
            $table->id('requested_tool_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('tool_id')->nullable();
            $table->unsignedBigInteger('testing_tool_id')->nullable();
            $table->string('description');
            $table->enum('status', ['pending', 'approved', 'denied'])->default('pending');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('project_id')->references('project_id')->on('projects')->onDelete('cascade');
            $table->foreign('tool_id')->references('tool_id')->on('development_tools')->onDelete('set null');
            $table->foreign('testing_tool_id')->references('testing_tool_id')->on('testing_tools')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requested_tools');
    }
};
