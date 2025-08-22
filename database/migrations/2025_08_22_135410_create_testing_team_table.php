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
        Schema::create('testing_teams', function (Blueprint $table) {
            $table->id("testing_team_id");
            $table->string('team_name');
            $table->integer('team_size');
            $table->string('specialization')->nullable();
            $table->unsignedBigInteger('manager_id');
            $table->timestamps();

            $table->foreign('manager_id')
                ->references('manager_id')
                ->on('project_managers')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('testing_team');
    }
};
