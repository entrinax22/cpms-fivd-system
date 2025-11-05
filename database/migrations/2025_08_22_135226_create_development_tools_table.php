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
        Schema::create('development_tools', function (Blueprint $table) {
            $table->id("tool_id");
            $table->unsignedBigInteger('team_id')->nullable();
            $table->string('tool_name');
            $table->string('tool_version')->nullable();
            $table->date('license_expiry_date')->nullable();
            $table->timestamps();

            $table->foreign('team_id')->references('team_id')->on('development_teams')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('development_tools');
    }
};
