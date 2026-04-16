<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('instructions', function (Blueprint $table) {
            $table->id();
            $table->string('unit_id')->nullable();
            $table->string('title');
            $table->enum('type', ['instruction', 'checklist']);
            $table->text('content')->nullable();
            $table->json('checklist_items')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('unit_id')
                ->references('id')
                ->on('units')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->index(['unit_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instructions');
    }
};
