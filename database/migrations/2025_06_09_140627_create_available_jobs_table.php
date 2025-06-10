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
       Schema::create('available_jobs', function (Blueprint $table) {
           $table->id();
           $table->timestamps();
           $table->foreignId('company_id')->constrained('users')->onDelete('cascade');
           $table->string('title');
           $table->text('description');
           $table->text('requirements');
           $table->enum('job_type', ['full_time', 'part_time', 'contract', 'internship']);
           $table->enum('experience_level', ['entry', 'junior', 'mid', 'senior']);
           $table->integer('salary_min')->nullable();
           $table->integer('salary_max')->nullable();
           $table->date('application_deadline')->nullable();
           $table->enum('status', ['active', 'closed', 'paused'])->default('active');
           $table->boolean('is_featured')->default(false);
           $table->boolean('is_remote')->default(false);
           $table->integer('applications_count')->default(0);


           $table->index('company_id');
           $table->index('job_type');
           $table->index('experience_level');
           $table->index('status');
           $table->index('application_deadline');
       });
   }


   /**
    * Reverse the migrations.
    */
   public function down(): void
   {
       Schema::dropIfExists('jobs');
   }
};



