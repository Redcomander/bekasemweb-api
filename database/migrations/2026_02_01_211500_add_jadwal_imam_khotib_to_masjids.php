<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('masjids', function (Blueprint $table) {
            // Jadwal Imam (text + file)
            $table->text('jadwal_imam')->nullable()->after('keterangan');
            $table->string('jadwal_imam_file')->nullable()->after('jadwal_imam');

            // Jadwal Khotib Jum'at (text + file)
            $table->text('jadwal_khotib')->nullable()->after('jadwal_imam_file');
            $table->string('jadwal_khotib_file')->nullable()->after('jadwal_khotib');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('masjids', function (Blueprint $table) {
            $table->dropColumn(['jadwal_imam', 'jadwal_imam_file', 'jadwal_khotib', 'jadwal_khotib_file']);
        });
    }
};
