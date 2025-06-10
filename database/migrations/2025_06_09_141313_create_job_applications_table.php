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
        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('job_id')->constrained('available_jobs')->onDelete('cascade');
            $table->enum('status', ['applied', 'reviewed', 'interviewed','hired', 'rejected'])->default('applied');
            $table->text('cover_letter')->nullable();
            $table->text('company_notes')->nullable();
            $table->date('applied_at')->useCurrent();

            $table->unique(['user_id', 'job_id']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_applications');
    }
};
