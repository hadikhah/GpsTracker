<?php

namespace App\Services;

class GpsSmoothingService
{
    public function gaussianKernel(float $x, float $sigma = 1.0): float
    {
        return exp(- (($x * $x) / (2 * $sigma * $sigma)));
    }

    public function smoothTrajectory(array $points, float $sigma = 1.5, int $windowSize = 5): array
    {
        $smoothedPoints = [];
        $n = count($points);

        for ($i = 0; $i < $n; $i++) {
            $weightSum = 0;
            $latSum = 0;
            $lonSum = 0;

            for ($j = max(0, $i - $windowSize); $j < min($n, $i + $windowSize + 1); $j++) {
                $distance = abs($i - $j);
                $weight = $this->gaussianKernel($distance, $sigma);

                $latSum += $points[$j]['latitude'] * $weight;
                $lonSum += $points[$j]['longitude'] * $weight;
                $weightSum += $weight;
            }

            $smoothedPoints[] = [
                'latitude' => $latSum / $weightSum,
                'longitude' => $lonSum / $weightSum,
                'speed' => $points[$i]['speed'],
                'date_time' => $points[$i]['date_time']
            ];
        }

        return $smoothedPoints;
    }
}
