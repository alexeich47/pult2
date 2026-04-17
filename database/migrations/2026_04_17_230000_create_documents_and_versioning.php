<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // New "documents" table for generic docs (the 1st of 3 pages in the Docs group).
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('unit_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('title');
            $table->string('category')->nullable(); // e.g. Политика / Регламент / Шаблон
            $table->text('content')->nullable();
            $table->string('version', 8)->default('0.1');
            $table->timestamps();

            $table->foreign('unit_id')->references('id')->on('units')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['unit_id']);
        });

        // Add "version" to existing instructions / processes tables, default "0.1".
        Schema::table('instructions', function (Blueprint $table) {
            $table->string('version', 8)->default('0.1')->after('checklist_items');
        });
        DB::table('instructions')->update(['version' => '0.1']);

        Schema::table('processes', function (Blueprint $table) {
            $table->string('version', 8)->default('0.1')->after('sort_order');
            $table->unsignedBigInteger('created_by')->nullable()->after('owner_id');
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });
        DB::table('processes')->update(['version' => '0.1']);
    }

    public function down(): void
    {
        Schema::table('processes', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn(['version', 'created_by']);
        });
        Schema::table('instructions', function (Blueprint $table) {
            $table->dropColumn('version');
        });
        Schema::dropIfExists('documents');
    }
};
