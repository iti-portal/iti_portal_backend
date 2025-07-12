<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            // Composite index for name-based searches
            $table->index(['first_name', 'last_name']);
            
            // Individual indexes for frequently searched fields
            $table->index('username');
            $table->index('phone');
            
            // Composite index for track/intake filtering
            $table->index(['track', 'intake']);
        });

        Schema::table('user_skills', function (Blueprint $table) {
            // Improve join performance with skills
            $table->index(['user_id', 'skill_id']);
        });

        Schema::table('skills', function (Blueprint $table) {
            // Faster skill name lookups
            $table->index('name');
        });
    }

    public function down()
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropIndex(['first_name', 'last_name']);
            $table->dropIndex(['username']);
            $table->dropIndex(['phone']);
            $table->dropIndex(['track', 'intake']);
        });

        Schema::table('user_skills', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'skill_id']);
        });

        Schema::table('skills', function (Blueprint $table) {
            $table->dropIndex(['name']);
        });
    }
};