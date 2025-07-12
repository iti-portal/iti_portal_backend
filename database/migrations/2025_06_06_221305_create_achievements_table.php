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
        Schema::create('achievements', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['award', 'certification', 'job', 'project']);
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('organization');
            $table->date('achieved_at');
            $table->string('image_path')->nullable();
            $table->string('certificate_url')->nullable();
            $table->string('project_url')->nullable();
        
            $table->integer('like_count')->default(0);
            $table->integer('comment_count')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('achievements');
    }
};
