<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mvr_daily_entries', function (Blueprint $table) {
            $table->dropUnique(['date']);
        });

        Schema::table('mvr_daily_entries', function (Blueprint $table) {
            $table->string('unit_id')->nullable()->after('id');
        });

        // Existing rows are PlayDuck data (seeded from PlayDuck screenshots).
        DB::table('mvr_daily_entries')->whereNull('unit_id')->update(['unit_id' => 'playduck']);

        Schema::table('mvr_daily_entries', function (Blueprint $table) {
            $table->foreign('unit_id')->references('id')->on('units')->nullOnDelete();
            $table->unique(['date', 'unit_id']);
        });
    }

    public function down(): void
    {
        Schema::table('mvr_daily_entries', function (Blueprint $table) {
            $table->dropUnique(['date', 'unit_id']);
            $table->dropForeign(['unit_id']);
            $table->dropColumn('unit_id');
        });

        Schema::table('mvr_daily_entries', function (Blueprint $table) {
            $table->unique('date');
        });
    }
};
