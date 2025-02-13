<?php

namespace App\Services;

use App\Services\GpsFilters\GpsFilterInterface;

class GpsFilterService
{
    private array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function addFilter(GpsFilterInterface $filter): self
    {
        $this->filters[] = $filter;
        return $this;
    }

    public function filterTrajectory(array $points): array
    {
        return array_reduce(
            $this->filters,
            fn(array $filtered, GpsFilterInterface $filter) => $filter->filter($filtered),
            $points
        );
    }
}
