<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Routine extends Model
{
    protected $fillable = [
        'athlete_id', 'name', 'week_start', 'status', 'notes',
    ];

    protected $casts = [
        'week_start' => 'date',
    ];

    public const DAYS = [
        1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves',
        5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo',
    ];

    public function athlete(): BelongsTo
    {
        return $this->belongsTo(Athlete::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(RoutineItem::class);
    }

    /** Items grouped by day_of_week (1..7), each sorted by position. */
    public function itemsByDay()
    {
        return $this->items()
            ->with('exercise')
            ->orderBy('position')
            ->get()
            ->groupBy('day_of_week');
    }

    /** Completion percentage across all items (0..100, integer). */
    public function progress(): int
    {
        $total = $this->items()->count();
        if ($total === 0) return 0;
        $done = $this->items()->where('completed', true)->count();
        return (int) round($done * 100 / $total);
    }
}
