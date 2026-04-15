<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('risk_entries', function (Blueprint $table) {
            $table->id();
            $table->string('display_id')->nullable()->unique(); // R-001 / I-001 / F-001 / W-001
            $table->enum('type', ['risk', 'issue', 'fuckup', 'workaround']);
            $table->string('name');
            $table->text('description');
            $table->string('owner_name'); // free text per legacy (no FK to employees)
            $table->string('status');
            $table->date('entry_date');
            $table->timestamps();

            $table->index(['type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('risk_entries');
    }
};
