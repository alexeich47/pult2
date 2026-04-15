<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ideas', function (Blueprint $table) {
            $table->id();
            $table->string('display_id')->nullable()->unique(); // e.g. ID-001 — set post-insert from id
            $table->string('unit_id');
            $table->unsignedBigInteger('author_id');
            $table->enum('priority', ['high', 'medium', 'low']);
            $table->enum('status', [
                'new',
                'under_review',
                'approved',
                'rejected',
                'in_progress',
                'done',
            ]);
            $table->string('title'); // legacy `idea` field
            $table->text('description');
            $table->text('impact');
            $table->timestamps();

            $table->foreign('unit_id')
                ->references('id')
                ->on('units')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign('author_id')
                ->references('id')
                ->on('employees')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->index(['unit_id', 'status']);
            $table->index(['priority', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ideas');
    }
};
