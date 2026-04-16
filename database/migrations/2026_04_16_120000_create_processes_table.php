<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('processes', function (Blueprint $table) {
            $table->id();
            $table->string('unit_id')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->string('category');
            $table->enum('maturity', ['not_documented', 'documented_testing', 'documented_digitized', 'fully_automated']);
            $table->string('document_url')->nullable();
            $table->string('tool')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('unit_id')
                ->references('id')
                ->on('units')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreign('owner_id')
                ->references('id')
                ->on('employees')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->index(['unit_id', 'maturity']);
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('processes');
    }
};
