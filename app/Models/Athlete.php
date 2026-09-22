<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Athlete extends Model
{
    protected $fillable = [
        'name', 'birthdate', 'sex', 'discipline', 'status', 'notes',
    ];

    protected $casts = [
        'birthdate' => 'date',
    ];

    public function measurements(): HasMany
    {
        return $this->hasMany(Measurement::class);
    }

    public function age(): ?int
    {
        return $this->birthdate?->age;
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
