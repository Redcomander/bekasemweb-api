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
        // Pivot: masjid_imam
        Schema::create('masjid_imam', function (Blueprint $table) {
            $table->foreignId('masjid_id')->constrained()->onDelete('cascade');
            $table->foreignId('imam_id')->constrained()->onDelete('cascade');
            $table->boolean('is_primary')->default(false); // Imam tetap
            $table->primary(['masjid_id', 'imam_id']);
            $table->timestamps();
        });

        // Pivot: masjid_khotib
        Schema::create('masjid_khotib', function (Blueprint $table) {
            $table->foreignId('masjid_id')->constrained()->onDelete('cascade');
            $table->foreignId('khotib_id')->constrained()->onDelete('cascade');
            $table->primary(['masjid_id', 'khotib_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('masjid_khotib');
        Schema::dropIfExists('masjid_imam');
    }
};
