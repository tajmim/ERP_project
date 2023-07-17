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
        Schema::create('dailywork_details', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();            
            $table->string('user_id')->nullable();
            $table->string('user_type')->nullable();
            $table->string('start_time')->nullable();
            $table->string('pause_time')->nullable();
            $table->string('resume_time')->nullable();
            $table->string('end_time')->nullable();
            $table->string('work_status')->nullable();
            $table->string('total_work_time')->nullable();
            $table->string('work_note')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dailywork_details');
    }
};
