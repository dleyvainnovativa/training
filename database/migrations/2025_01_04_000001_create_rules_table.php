<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('metric_id')->constrained()->cascadeOnDelete();

            // Condition: when does this rule fire against an athlete's latest value?
            //   below      -> value < threshold
            //   above      -> value > threshold
            //   below_min  -> value < metric.min_range
            //   above_max  -> value > metric.max_range
            //   outside    -> value < min_range OR value > max_range
            $table->enum('condition', ['below', 'above', 'below_min', 'above_max', 'outside']);
            $table->decimal('threshold', 12, 3)->nullable(); // required for below/above

            // Recommendation when it fires.
            $table->string('recommend_category');           // matches Exercise.category
            $table->enum('recommend_intensity', ['low', 'medium', 'high'])->nullable(); // optional preference
            $table->unsignedTinyInteger('weight')->default(1); // 1..10, contributes to score

            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rules');
    }
};
