<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mvr_daily_entries', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->decimal('plan', 14, 2)->default(0);
            $table->decimal('fact', 14, 2)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->timestamps();

            $table->unique('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mvr_daily_entries');
    }
};
