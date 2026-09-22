<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Measurement extends Model
{
    protected $fillable = [
        'athlete_id', 'metric_id', 'value', 'measured_at', 'note',
    ];

    protected $casts = [
        'value'       => 'decimal:3',
        'measured_at' => 'date',
    ];

    public function athlete(): BelongsTo
    {
        return $this->belongsTo(Athlete::class);
    }

    public function metric(): BelongsTo
    {
        return $this->belongsTo(Metric::class);
    }
}
