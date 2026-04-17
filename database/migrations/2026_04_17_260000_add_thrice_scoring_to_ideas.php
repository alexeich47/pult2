<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ideas', function (Blueprint $table) {
            // THRICE — 6-dimension scoring framework (1-10 each, sum = total).
            // Extended ICE: Impact, Confidence, Effort + Time, Headcount, Reach.
            $table->unsignedTinyInteger('score_time')->nullable()->after('impact');      // T — speed to result
            $table->unsignedTinyInteger('score_headcount')->nullable()->after('score_time'); // H — fewer people = higher
            $table->unsignedTinyInteger('score_reach')->nullable()->after('score_headcount');  // R — users/scope reached
            $table->unsignedTinyInteger('score_impact')->nullable()->after('score_reach');     // I — key metric/$ impact
            $table->unsignedTinyInteger('score_confidence')->nullable()->after('score_impact'); // C — confidence in result
            $table->unsignedTinyInteger('score_effort')->nullable()->after('score_confidence'); // E — simpler = higher
        });
    }

    public function down(): void
    {
        Schema::table('ideas', function (Blueprint $table) {
            $table->dropColumn([
                'score_time', 'score_headcount', 'score_reach',
                'score_impact', 'score_confidence', 'score_effort',
            ]);
        });
    }
};
