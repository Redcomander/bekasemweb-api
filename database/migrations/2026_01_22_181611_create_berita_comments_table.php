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
        Schema::create('berita_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('berita_id')->constrained('beritas')->onDelete('cascade');
            $table->string('nama');
            $table->string('email')->nullable();
            $table->text('komentar');
            $table->boolean('is_approved')->default(true);
            $table->boolean('is_spam')->default(false);
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['berita_id', 'is_approved']);
            $table->index('ip_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('berita_comments');
    }
};
