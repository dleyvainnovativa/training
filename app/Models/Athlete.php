<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Athlete extends Model
{
    /**
     * Metric name(s) treated as body weight for BMI purposes. If you rename the
     * weight metric, add the new name here (matching is case-insensitive).
     */
    public const WEIGHT_METRIC_NAMES = ['Peso corporal', 'Peso', 'Peso (kg)'];

    protected $fillable = [
        'name', 'birthdate', 'sex', 'height_cm', 'dominant_hand',
        'discipline', 'position', 'phone', 'emergency_contact',
        'status', 'notes',
    ];

    protected $casts = [
        'birthdate' => 'date',
        'height_cm' => 'decimal:1',
    ];

    public function measurements(): HasMany
    {
        return $this->hasMany(Measurement::class);
    }

    public function age(): ?int
    {
        return $this->birthdate?->age;
    }

    public function dominantHandLabel(): ?string
    {
        return match ($this->dominant_hand) {
            'right' => 'Diestro',
            'left'  => 'Zurdo',
            'ambi'  => 'Ambidiestro',
            default => null,
        };
    }

    /**
     * Latest recorded body weight (kg), taken from the weight metric's most
     * recent measurement. Returns null if no weight has been recorded.
     */
    public function latestWeightKg(): ?float
    {
        $weight = $this->measurements()
            ->whereHas('metric', fn ($q) =>
                $q->whereIn('name', self::WEIGHT_METRIC_NAMES))
            ->orderByDesc('measured_at')
            ->first();

        return $weight ? (float) $weight->value : null;
    }

    /**
     * BMI derived on the fly from static height + latest weight measurement.
     * Not stored, so it's always current. Returns null if either is missing.
     */
    public function bmi(): ?float
    {
        $weight = $this->latestWeightKg();
        $height = $this->height_cm ? (float) $this->height_cm : null;

        if (! $weight || ! $height || $height <= 0) {
            return null;
        }

        $m = $height / 100;
        return round($weight / ($m * $m), 1);
    }

    /**
     * Latest value per metric for this athlete.
     * Returns a collection keyed by metric_id.
     */
    public function latestByMetric()
    {
        return $this->measurements()
            ->with('metric')
            ->get()
            ->groupBy('metric_id')
            ->map(fn ($rows) => $rows->sortByDesc('measured_at')->first());
    }
}
