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
        Schema::table('available_jobs', function (Blueprint $table) {

            $table->Integer('review_applications')->default(0);
            $table->Integer('interview_applications')->default(0);
            $table->Integer('hired_applications')->default(0);
            $table->Integer('rejected_applications')->default(0);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('available_jobs', function (Blueprint $table) {

            $table->dropColumn([
                'review_applications',
                'interview_applications',
                'hired_applications',
                'rejected_applications',
            ]);
        });
    }
};
