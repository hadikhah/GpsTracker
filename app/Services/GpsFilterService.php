<?php

namespace App\Services;

use App\Services\GpsFilters\GpsFilterInterface;

class GpsFilterService
{
    private array $filters;
    private array $points;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function addFilter(GpsFilterInterface $filter): self
    {
        $this->filters[] = $filter;
        return $this;
    }

    public function addTracks(array $originalPoints)
    {
        $this->points = $originalPoints;

        return $this;
    }

    public function filterTrajectory(): array
    {
        foreach ($this->filters as $key => $filter) {
            $this->points = $this->filters[$key]->filter($this->points);
        }

        return $this->points;
    }
}
