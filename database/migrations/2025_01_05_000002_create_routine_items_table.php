<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('routine_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('routine_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exercise_id')->constrained()->cascadeOnDelete();
            // 1 = Monday ... 7 = Sunday (ISO-8601 day of week).
            $table->unsignedTinyInteger('day_of_week');
            $table->unsignedSmallInteger('position')->default(0); // order within the day
            $table->string('prescription')->nullable();           // e.g. "4x400m", "3x10"
            $table->boolean('completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['routine_id', 'day_of_week', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('routine_items');
    }
};
