<?php

namespace App\Http\Controllers;

use App\Models\Athlete;
use App\Services\RecommendationEngine;

class RecommendationController extends Controller
{
    public function __construct(
        private readonly RecommendationEngine $engine
    ) {}

    /** JSON recommendations for an athlete (used by the profile panel). */
    public function forAthlete(Athlete $athlete)
    {
        $result = $this->engine->forAthlete($athlete);

        return response()->json([
            'athlete'         => $athlete->name,
            'triggered'       => $result['triggered'],
            'recommendations' => $result['recommendations'],
        ]);
    }
}
