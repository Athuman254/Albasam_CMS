<?php

namespace App\Services;

use App\Models\Institution;

class GpsService
{
    /**
     * Check if given coordinates are within the school's geofence
     *
     * @param float $latitude
     * @param float $longitude
     * @return array ['within_geofence' => bool, 'distance' => float]
     */
    public function isWithinGeofence(float $latitude, float $longitude): array
    {
        $institution = Institution::first();

        if (!$institution || !$institution->latitude || !$institution->longitude) {
            return [
                'within_geofence' => false,
                'distance' => null,
                'error' => 'School GPS coordinates not configured'
            ];
        }

        $distance = $this->calculateDistance(
            $latitude,
            $longitude,
            $institution->latitude,
            $institution->longitude
        );

        $radius = $institution->geofence_radius ?? 100; // Default 100 meters

        return [
            'within_geofence' => $distance <= $radius,
            'distance' => round($distance, 2),
            'radius' => $radius,
            'school_coordinates' => [
                'latitude' => $institution->latitude,
                'longitude' => $institution->longitude
            ]
        ];
    }

    /**
     * Calculate distance between two GPS coordinates using Haversine formula
     *
     * @param float $lat1
     * @param float $lng1
     * @param float $lat2
     * @param float $lng2
     * @return float Distance in meters
     */
    public function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000; // Earth's radius in meters

        $latDiff = deg2rad($lat2 - $lat1);
        $lngDiff = deg2rad($lng2 - $lng1);

        $a = sin($latDiff / 2) * sin($latDiff / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($lngDiff / 2) * sin($lngDiff / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Get school GPS coordinates
     *
     * @return array|null
     */
    public function getSchoolCoordinates(): ?array
    {
        $institution = Institution::first();

        if (!$institution || !$institution->latitude || !$institution->longitude) {
            return null;
        }

        return [
            'latitude' => $institution->latitude,
            'longitude' => $institution->longitude,
            'geofence_radius' => $institution->geofence_radius ?? 100
        ];
    }

    /**
     * Validate GPS coordinates format
     *
     * @param float $latitude
     * @param float $longitude
     * @return bool
     */
    public function validateCoordinates(float $latitude, float $longitude): bool
    {
        return $latitude >= -90 && $latitude <= 90 &&
            $longitude >= -180 && $longitude <= 180;
    }
}
