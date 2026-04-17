<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_page_access', function (Blueprint $table) {
            $table->id();
            $table->string('page_slug');
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['page_slug', 'employee_id']);
            $table->index('page_slug');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_page_access');
    }
};
