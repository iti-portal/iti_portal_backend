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
        Schema::table('job_applications', function (Blueprint $table) {
            $table->timestamp('cv_downloaded_at')->nullable()->after('cv_path');
            $table->timestamp('profile_viewed_at')->nullable()->after('cv_downloaded_at');
            $table->boolean('is_reviewed')->default(false)->after('profile_viewed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropColumn(['cv_downloaded_at', 'profile_viewed_at', 'is_reviewed']);
        });
    }
};
