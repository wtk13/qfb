<?php

declare(strict_types=1);

namespace Domain\Reddit\Port;

interface AiStrategistInterface
{
    /**
     * @return array{summary: string, working_well: array, needs_improvement: array, recommendations: array, phase_assessment: string}
     */
    public function analyzeWeeklyMetrics(array $metricsContext): array;
}
