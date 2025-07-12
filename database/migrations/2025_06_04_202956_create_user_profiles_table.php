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
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('username')->unique();
            $table->text('summary')->nullable();
            $table->string('phone');
            $table->string('whatsapp')->nullable();
            $table->string('linkedin')->nullable();
            $table->string('github')->nullable();
            $table->string('portfolio_url')->nullable();
            $table->string('profile_picture')->nullable();
            $table->string('cover_photo')->nullable();
            $table->string('branch');
            $table->boolean('available_for_freelance')->default(false);
            $table->enum('program', ['ptp', 'itp']);
            $table->string('track');
            $table->string('intake');
            $table->enum('student_status', ['current', 'graduate'])->nullable();
            $table->string('nid_front_image')->nullable();
            $table->string('nid_back_image')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
