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
            $table->id();
            $table->string('project_name')->nullable();
            $table->string('project_client')->nullable();
            $table->string('project_budget')->nullable();
            $table->string('project_technology')->nullable();
            $table->string('project_type')->nullable();
            $table->string('project_developer')->nullable();
            $table->string('project_manager')->nullable();
            $table->string('project_documents')->nullable();
            $table->string('project_contact')->nullable();
            $table->timestamps();
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
