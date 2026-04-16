<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('okr_entries', function (Blueprint $table) {
            $table->id();
            $table->string('unit_id')->nullable();
            $table->enum('type', ['north_star', 'objective', 'key_result']);
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('period'); // e.g. "Q2 2025", "2025"
            $table->unsignedTinyInteger('progress')->default(0);
            $table->enum('status', ['active', 'completed', 'cancelled'])->default('active');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('unit_id')
                ->references('id')
                ->on('units')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreign('parent_id')
                ->references('id')
                ->on('okr_entries')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->index(['unit_id', 'type']);
            $table->index('parent_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('okr_entries');
    }
};
