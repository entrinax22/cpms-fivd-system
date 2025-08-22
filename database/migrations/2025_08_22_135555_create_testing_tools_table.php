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
        Schema::create('testing_tools', function (Blueprint $table) {
            $table->id("testing_tool_id");
            $table->string('testing_tool_name');
            $table->unsignedBigInteger('testing_team_id');
            $table->string('license_key')->nullable();
            $table->timestamps();

            $table->foreign('testing_team_id')
                ->references('testing_team_id')
                ->on('testing_teams')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('testing_tools');
    }
};
