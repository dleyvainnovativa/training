<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rule extends Model
{
    protected $fillable = [
        'name', 'metric_id', 'condition', 'threshold',
        'recommend_category', 'recommend_intensity', 'weight', 'active',
    ];

    protected $casts = [
        'threshold' => 'decimal:3',
        'weight'    => 'integer',
        'active'    => 'boolean',
    ];

    public const CONDITIONS = [
        'below'     => 'Por debajo de umbral',
        'above'     => 'Por encima de umbral',
        'below_min' => 'Por debajo del rango mínimo',
        'above_max' => 'Por encima del rango máximo',
        'outside'   => 'Fuera del rango',
    ];

    public function metric(): BelongsTo
    {
        return $this->belongsTo(Metric::class);
    }

    /** Whether this condition needs a manual threshold value. */
    public function needsThreshold(): bool
    {
        return in_array($this->condition, ['below', 'above'], true);
    }

    /** Human phrase for display, e.g. "VO2 máx por debajo de 45". */
    public function describe(): string
    {
        $metric = $this->metric?->name ?? 'métrica';
        return match ($this->condition) {
            'below'     => "{$metric} por debajo de {$this->plainThreshold()}",
            'above'     => "{$metric} por encima de {$this->plainThreshold()}",
            'below_min' => "{$metric} por debajo del mínimo",
            'above_max' => "{$metric} por encima del máximo",
            'outside'   => "{$metric} fuera del rango",
            default     => $metric,
        };
    }

    private function plainThreshold(): string
    {
        if ($this->threshold === null) return '—';
        return rtrim(rtrim((string) $this->threshold, '0'), '.');
    }
}
