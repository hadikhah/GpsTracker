<?php

namespace App\Http\Controllers;

use App\Models\GpsTrack;
use App\Services\GpsSmoothingService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GpsTrackerController extends Controller
{
    public function index($imei, GpsSmoothingService $service)
    {
        $devices = GpsTrack::query()->orderBy('date_time')->groupBy("imei")->get();

        // dd($devices);

        $tracks = GpsTrack::query()->orderBy('date_time')->where("imei", $imei)->get()->toArray();

        $smoothedTracks = $service->smoothTrajectory($tracks);

        return Inertia::render('GPSTrack', [
            'originalTracks' => $tracks,
            'devices' => $devices,
            'smoothedTracks' => $smoothedTracks
        ]);
    }
}
