<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exercise extends Model
{
    protected $fillable = [
        'name', 'category', 'tags', 'intensity',
        'equipment', 'description', 'active',
    ];

    protected $casts = [
        'tags'   => 'array',
        'active' => 'boolean',
    ];

    /** Shared category vocabulary — keep aligned with metrics for the engine. */
    public const CATEGORIES = [
        'Resistencia', 'Fuerza', 'Velocidad', 'Potencia', 'Movilidad',
    ];

    public const INTENSITIES = [
        'low'    => 'Baja',
        'medium' => 'Media',
        'high'   => 'Alta',
    ];

    public function intensityLabel(): string
    {
        return self::INTENSITIES[$this->intensity] ?? $this->intensity;
    }
}
