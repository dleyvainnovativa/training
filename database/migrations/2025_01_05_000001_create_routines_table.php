<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('routines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('athlete_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->date('week_start');           // Monday of the routine week
            $table->enum('status', ['draft', 'active', 'archived'])->default('draft');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['athlete_id', 'week_start']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('routines');
    }
};
