<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exercises', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            // Structural bridge to metrics: same category vocabulary.
            $table->string('category')->nullable();
            // Finer, free-form context: muscle group, equipment, etc.
            $table->json('tags')->nullable();
            $table->enum('intensity', ['low', 'medium', 'high'])->default('medium');
            $table->string('equipment')->nullable();
            $table->text('description')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exercises');
    }
};
