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
        Schema::create('alumni_services', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('alumni_id')->constrained('users')->onDelete('cascade');
            $table->enum('service_type', ['freelance', 'business_session', 'course_teaching']);
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('feedback')->nullable();
            $table->boolean('has_taught_or_presented')->default(false);
            $table->enum('evaluation', ['positive', 'neutral', 'negative'])->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alumni_services');
    }
};
