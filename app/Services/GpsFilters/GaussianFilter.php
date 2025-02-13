<?php

namespace App\Services\GpsFilters;

class GaussianFilter implements GpsFilterInterface
{
    private float $sigma; // Standard deviation for the Gaussian kernel
    private int $windowSize; // Size of the smoothing window

    public function __construct(float $sigma = 1.4, int $windowSize = 7)
    {
        $this->sigma = $sigma;
        $this->windowSize = $windowSize;
    }

    public function filter(array $points): array
    {
        return $this->applyGaussianSmoothing($points);
    }

    private function applyGaussianSmoothing(array $points): array
    {
        $smoothedPoints = [];
        $totalPoints = count($points);

        for ($i = 0; $i < $totalPoints; $i++) {
            $smoothedPoint = $this->smoothPoint($points, $i);
            $smoothedPoints[] = $smoothedPoint;
        }

        return $smoothedPoints;
    }

    private function smoothPoint(array $points, int $index): array
    {
        $weightSum = 0;
        $latSum = 0;
        $lonSum = 0;

        $start = max(0, $index - $this->windowSize);
        $end = min(count($points), $index + $this->windowSize + 1);

        for ($j = $start; $j < $end; $j++) {
            $distance = abs($index - $j);
            $weight = $this->calculateGaussianWeight($distance);

            $latSum += $points[$j]['latitude'] * $weight;
            $lonSum += $points[$j]['longitude'] * $weight;
            $weightSum += $weight;
        }

        return array_merge($points[$index], [
            'latitude' => $latSum / $weightSum,
            'longitude' => $lonSum / $weightSum,
        ]);
    }

    private function calculateGaussianWeight(float $distance): float
    {
        return exp(- ($distance * $distance) / (2 * $this->sigma * $this->sigma));
    }
}
