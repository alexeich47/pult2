<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('url')->nullable();
            $table->string('category');
            $table->string('unit_id');
            $table->decimal('cost', 12, 2)->default(0);
            $table->enum('currency', ['USD', 'EUR', 'UAH', 'RUB'])->default('USD');
            $table->enum('billing', ['monthly', 'yearly', 'once'])->default('monthly');
            $table->date('next_payment')->nullable();
            $table->enum('status', ['active', 'trial', 'inactive'])->default('active');
            $table->timestamps();

            $table->foreign('unit_id')
                ->references('id')
                ->on('units')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->index(['unit_id', 'status']);
            $table->index(['billing', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
