<?php

namespace Database\Seeders;

use App\Models\GpsTrack;
use Illuminate\Database\Seeder;

class GpsTrackSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing records
        GpsTrack::truncate();

        // Read the file
        $filePath = database_path('gps_tracks.txt');

        if (!file_exists($filePath)) {
            $this->command->error('GPS tracks data file not found!');
            return;
        }

        $fileContent = file_get_contents($filePath);
        $lines = explode("\n", $fileContent);


        foreach ($lines as $line) {
            if (empty(trim($line))) {
                continue;
            }

            // Remove the square brackets from each line
            try {
                $line = trim($line, '[');
                $line = str_replace("]", "", $line);
                $data = json_decode($line, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    $this->command->warn("Skipping invalid JSON line: $line");
                    continue;
                }

                GpsTrack::create([
                    'latitude' => $data['latitude'],
                    'longitude' => $data['longitude'],
                    'speed' => $data['speed'],
                    'status' => $data['status'],
                    'date_time' => $data['date_time'],
                    'imei' => $data['imei'],
                    'stopped' => $data['stopped'],
                    'stoppage_amount' => $data['stoppage_amount'],
                    'is_start_point' => $data['is_start_point'],
                    'is_end_point' => $data['is_end_point'],
                ]);
            } catch (\Exception $e) {
                $this->command->error("Error processing line: " . $e->getMessage());
                continue;
            }
        }

        $this->command->info('GPS tracks data has been imported successfully!');
    }
}
