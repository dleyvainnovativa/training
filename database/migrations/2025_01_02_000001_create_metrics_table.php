<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('metrics', function (Blueprint $table) {
            $table->id();
            $table->string('name');                    // e.g. "VO2 máx"
            $table->string('unit')->nullable();        // e.g. "ml/kg/min"
            $table->string('category')->nullable();    // e.g. "Resistencia", "Fuerza"
            $table->text('reference')->nullable();     // free-text standard/notes
            // Direction the metric should move to be "better".
            $table->enum('direction', ['higher', 'lower'])->default('higher');
            $table->decimal('min_range', 10, 3)->nullable(); // optional expected floor
            $table->decimal('max_range', 10, 3)->nullable(); // optional expected ceiling
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('metrics');
    }
};
