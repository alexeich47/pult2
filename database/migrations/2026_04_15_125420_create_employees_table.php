<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('unit_id');
            $table->unsignedBigInteger('manager_id')->nullable();
            $table->string('name')->nullable(); // null => vacancy
            $table->string('position');
            $table->string('department');
            $table->string('email')->nullable();
            $table->string('telegram')->nullable();
            $table->enum('status', ['active', 'vacancy', 'fired'])->default('active');
            $table->enum('work_stage', ['onboarding', 'probation', 'employee', 'offboarding'])->default('employee');
            $table->timestamps();

            $table->foreign('unit_id')
                ->references('id')
                ->on('units')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign('manager_id')
                ->references('id')
                ->on('employees')
                ->nullOnDelete();

            $table->index(['unit_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
