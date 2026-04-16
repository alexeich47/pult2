<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rnd_projects', function (Blueprint $table) {
            $table->id();
            $table->string('unit_id')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('owner_id');
            $table->enum('priority', ['critical', 'high', 'medium', 'low']);
            $table->enum('status', [
                'idea',
                'research',
                'testing',
                'pilot',
                'scaling',
                'paused',
                'killed',
                'completed',
            ]);
            $table->decimal('budget', 12, 2)->nullable();
            $table->enum('currency', ['USD', 'EUR', 'UAH', 'RUB'])->default('USD');
            $table->date('deadline')->nullable();
            $table->text('success_criteria');
            $table->text('kill_criteria');
            $table->date('started_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('unit_id')
                ->references('id')
                ->on('units')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreign('owner_id')
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
        Schema::dropIfExists('rnd_projects');
    }
};
