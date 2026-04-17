<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contractors', function (Blueprint $table) {
            $table->id();
            $table->string('unit_id')->nullable();
            $table->string('name');
            $table->string('specialty')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->enum('status', ['active', 'paused', 'ended'])->default('active');
            $table->date('started_at')->nullable();
            $table->date('ended_at')->nullable();
            $table->string('rate')->nullable(); // free text: "$50/hour", "$3000/month"
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('unit_id')->references('id')->on('units')->nullOnDelete();
            $table->index(['unit_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contractors');
    }
};
