<?php

namespace App\Http\Controllers;

use App\Models\GpsTrack;
use App\Services\GpsFilters\DbscanFilter;
use App\Services\GpsFilters\GaussianFilter;
use App\Services\GpsFilters\KalmanFilter;
use App\Services\GpsFilterService;
use Inertia\Inertia;

class GpsTrackerController extends Controller
{


    public function index()
    {
        $devices = GpsTrack::query()->orderBy('date_time')->groupBy("imei")->get();

        return Inertia::render('GpsDevicesList', compact("devices"));
    }

    public function show($imei, GpsFilterService $service, DbscanFilter $dbscanFilter, KalmanFilter $kalmanFilter, GaussianFilter $gaussianFilter)
    {
        $devices = GpsTrack::query()->orderBy('date_time')->groupBy("imei")->get();

        $originalTracks = GpsTrack::query()->orderBy('date_time')->where("imei", $imei)->get()
            ->toArray();

        $smoothedTracks = $service
            ->addFilter($dbscanFilter)
            ->addFilter($kalmanFilter)
            ->addFilter($gaussianFilter)
            ->filterTrajectory($originalTracks);

        return Inertia::render('GPSTrack', compact(
            'originalTracks',
            'devices',
            'smoothedTracks'
        ));
    }
}
