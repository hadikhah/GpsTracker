<?php

namespace App\Services\GpsFilters;

class DbscanFilter implements GpsFilterInterface
{
    private float $epsilon; // Maximum distance between points
    private int $minPoints; // Minimum points to form a cluster

    public function __construct(float $epsilon = 300, int $minPoints = 2)
    {
        $this->epsilon = $epsilon;
        $this->minPoints = $minPoints;
    }

    public function filter(array $points): array
    {
        $clusters = [];
        $noise = [];
        $visited = [];

        foreach ($points as $i => $point) {
            if (isset($visited[$i])) {
                continue;
            }

            $visited[$i] = true;
            $neighbors = $this->findNeighbors($points, $i);

            if (count($neighbors) < $this->minPoints) {
                $noise[] = $i;
                continue;
            }

            $cluster = [$i];
            $clusters[] = $cluster;
        }

        // Keep only points that are part of clusters
        $validPoints = [];
        foreach ($clusters as $cluster) {
            foreach ($cluster as $pointIndex) {
                $validPoints[] = $points[$pointIndex];
            }
        }

        return $validPoints;
    }

    private function findNeighbors(array $points, int $pointIndex): array
    {
        $neighbors = [];
        foreach ($points as $i => $point) {
            if ($i !== $pointIndex && $this->calculateDistance($points[$pointIndex], $point) <= $this->epsilon) {
                $neighbors[] = $i;
            }
        }
        return $neighbors;
    }

    private function calculateDistance(array $point1, array $point2): float
    {
        $lat1 = (float)$point1['latitude'];
        $lon1 = (float)$point1['longitude'];
        $lat2 = (float)$point2['latitude'];
        $lon2 = (float)$point2['longitude'];

        // return sqrt(pow($lat2 - $lat1, 2) + pow($lon2 - $lon1, 2));

        $earthRadius = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);
        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
