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
        Schema::create('projects', function (Blueprint $table) {
            $table->id("project_id");
            $table->string('project_name');
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('manager_id');
            $table->date('start_date');
            $table->date('estimated_end_date')->nullable();
            $table->string('project_description')->nullable();
            $table->enum('status', ['Pre_Construction', 'Design_&_Approval', 'Procurement_of_Materials', 'Site_Preparation', 'Construction_Ongoing', 'Inspection_&_Quality_Check', 'Completed/Turnover', 'On_Hold'])->default('Pre_Construction');
            $table->timestamps();

            $table->foreign('client_id')
                ->references('client_id')
                ->on('clients')
                ->onDelete('cascade');

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
        Schema::dropIfExists('projects');
    }
};
