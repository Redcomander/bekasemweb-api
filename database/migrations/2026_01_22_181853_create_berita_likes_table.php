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
        Schema::create('berita_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('berita_id')->constrained('beritas')->onDelete('cascade');
            $table->string('ip_address', 45);
            $table->string('fingerprint')->nullable(); // Browser fingerprint for additional tracking
            $table->timestamps();

            $table->unique(['berita_id', 'ip_address']);
            $table->index('berita_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('berita_likes');
    }
};
