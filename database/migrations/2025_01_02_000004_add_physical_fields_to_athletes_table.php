<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds physical / contact profile fields to athletes.
 *
 * Note: body weight is intentionally NOT a column here — it changes over time
 * and is tracked as a Metric ("Peso corporal") via the existing measurement
 * system, so it gets history charts and feeds the recommendation engine.
 * Height is a static column since it's effectively constant for adults, and
 * BMI is derived on the fly from height + latest weight measurement.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('athletes', function (Blueprint $table) {
            $table->decimal('height_cm', 5, 1)->nullable()->after('sex');   // e.g. 178.5
            $table->enum('dominant_hand', ['right', 'left', 'ambi'])->nullable()->after('height_cm');
            $table->string('position', 100)->nullable()->after('discipline'); // e.g. drive/revés, singles
            $table->string('phone', 40)->nullable()->after('position');
            $table->string('emergency_contact', 200)->nullable()->after('phone');
        });
    }

    public function down(): void
    {
        Schema::table('athletes', function (Blueprint $table) {
            $table->dropColumn(['height_cm', 'dominant_hand', 'position', 'phone', 'emergency_contact']);
        });
    }
};
