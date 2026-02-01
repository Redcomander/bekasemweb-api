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
        // Add indexes to beritas table for better performance
        Schema::table('beritas', function (Blueprint $table) {
            $table->index('status');
            $table->index('created_at');
            $table->index('updated_at');
            $table->index('view_count');
        });

        // Add indexes to berita_likes table
        Schema::table('berita_likes', function (Blueprint $table) {
            $table->index('created_at');
        });

        // Add indexes to berita_comments table
        Schema::table('berita_comments', function (Blueprint $table) {
            $table->index('created_at');
            $table->index(['is_approved', 'is_spam']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('beritas', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['updated_at']);
            $table->dropIndex(['view_count']);
        });

        Schema::table('berita_likes', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
        });

        Schema::table('berita_comments', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
            $table->dropIndex(['is_approved', 'is_spam']);
        });
    }
};
