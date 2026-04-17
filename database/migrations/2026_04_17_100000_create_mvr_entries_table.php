<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mvr_entries', function (Blueprint $table) {
            $table->id();
            $table->string('unit_id')->nullable();
            $table->integer('year');
            $table->integer('month');
            $table->decimal('target', 14, 2)->default(0);
            $table->decimal('actual', 14, 2)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->timestamps();

            $table->unique(['unit_id', 'year', 'month']);
            $table->foreign('unit_id')->references('id')->on('units')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mvr_entries');
    }
};
