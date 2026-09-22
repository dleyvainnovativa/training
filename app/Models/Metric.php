<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Metric extends Model
{
    protected $fillable = [
        'name', 'unit', 'category', 'reference',
        'direction', 'min_range', 'max_range', 'active',
    ];

    protected $casts = [
        'min_range' => 'decimal:3',
        'max_range' => 'decimal:3',
        'active'    => 'boolean',
    ];

    public function measurements(): HasMany
    {
        return $this->hasMany(Measurement::class);
    }

    /** True when a higher value is the desired direction. */
    public function higherIsBetter(): bool
    {
        return $this->direction === 'higher';
    }
}
