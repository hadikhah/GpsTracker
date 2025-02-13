<?php

namespace App\Services\GpsFilters;

class KalmanFilter implements GpsFilterInterface
{
    private float $processNoise; // Process noise (q)
    private float $measurementNoise; // Measurement noise (r)
    private float $estimationError; // Estimation error (p)
    private float $state; // State (x)
    private float $kalmanGain; // Kalman gain (k)

    public function __construct(float $processNoise = 0.0005, float $measurementNoise = 0.000005)
    {
        $this->processNoise = $processNoise;
        $this->measurementNoise = $measurementNoise;
        $this->estimationError = 1.0;
        $this->state = 0.0;
        $this->kalmanGain = 0.0;
    }

    public function filter(array $points): array
    {
        $filteredPoints = $this->applyKalmanFilter($points);
        return $this->interpolateMissingPoints($filteredPoints);
    }

    private function applyKalmanFilter(array $points): array
    {
        $filteredPoints = [];
        $latitudeFilter = clone $this;
        $longitudeFilter = clone $this;

        foreach ($points as $point) {
            $filteredLatitude = $latitudeFilter->updateState((float)$point['latitude']);
            $filteredLongitude = $longitudeFilter->updateState((float)$point['longitude']);

            $filteredPoints[] = array_merge($point, [
                'latitude' => $filteredLatitude,
                'longitude' => $filteredLongitude,
            ]);
        }

        return $filteredPoints;
    }

    private function updateState(float $measurement): float
    {
        // Prediction
        $this->estimationError += $this->processNoise;

        // Update
        $this->kalmanGain = $this->estimationError / ($this->estimationError + $this->measurementNoise);
        $this->state += $this->kalmanGain * ($measurement - $this->state);
        $this->estimationError *= (1 - $this->kalmanGain);

        return $this->state;
    }

    private function interpolateMissingPoints(array $points): array
    {
        $interpolatedPoints = [];
        $totalPoints = count($points);

        for ($i = 0; $i < $totalPoints - 1; $i++) {
            $interpolatedPoints[] = $points[$i];

            // Skip interpolation between two stopped points
            if ($points[$i]['stopped'] && $points[$i + 1]['stopped']) {
                continue;
            }

            $timeDifference = strtotime($points[$i + 1]['date_time']) - strtotime($points[$i]['date_time']);

            if ($timeDifference > 10) {
                $steps = ceil($timeDifference / 5);
                $this->addInterpolatedPoints(
                    $interpolatedPoints,
                    $points[$i],
                    $points[$i + 1],
                    $steps
                );
            }
        }

        if ($totalPoints > 1) {
            $interpolatedPoints[] = $points[$totalPoints - 1];
        }

        return $interpolatedPoints;
    }

    private function addInterpolatedPoints(array &$interpolatedPoints, array $currentPoint, array $nextPoint, int $steps): void
    {
        $baseLatitude = $currentPoint['stopped'] ? $currentPoint['latitude'] : $currentPoint['latitude'];
        $baseLongitude = $currentPoint['stopped'] ? $currentPoint['longitude'] : $currentPoint['longitude'];

        for ($j = 1; $j < $steps; $j++) {
            $ratio = $j / $steps;
            $interpolatedPoints[] = [
                'latitude' => $baseLatitude + ($nextPoint['latitude'] - $baseLatitude) * $ratio,
                'longitude' => $baseLongitude + ($nextPoint['longitude'] - $baseLongitude) * $ratio,
                'interpolated' => true,
            ];
        }
    }
}
