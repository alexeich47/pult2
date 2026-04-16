<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meetings', function (Blueprint $table) {
            $table->id();
            $table->string('unit_id')->nullable();
            $table->string('title');
            $table->enum('type', ['standup', 'weekly', 'monthly', 'one_on_one', 'retro', 'planning', 'other']);
            $table->dateTime('scheduled_at');
            $table->unsignedInteger('duration_minutes')->default(30);
            $table->string('location')->nullable();
            $table->text('agenda')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['scheduled', 'completed', 'cancelled'])->default('scheduled');
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

            $table->index(['unit_id', 'status']);
            $table->index('scheduled_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meetings');
    }
};
