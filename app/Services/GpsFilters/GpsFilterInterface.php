<?php

namespace App\Services\GpsFilters;


interface GpsFilterInterface
{
    public function filter(array $points): array;
}
