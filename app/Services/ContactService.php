<?php

namespace App\Services;

class ContactService
{
    /**
     * Get teacher contact numbers from the database and format them into an array
     *
     * @param array $teacherIds Array of teacher IDs to look up
     * @return array Array of formatted phone numbers
     */
    public function getTeacherNumbers(array $teacherIds): array
    {
        return Contact::whereIn('id', $teacherIds)
            ->pluck('phone_number')
            ->map(fn($number) => trim($number))
            ->toArray();
    }

    /**
     * Convert a comma-separated string of numbers into an array
     *
     * @param string $numbers Comma-separated phone numbers
     * @return array Clean array of numbers
     */
    public function formatNumberString(string $numbers): array
    {
        return array_map(
            fn($number) => trim($number),
            explode(',', $numbers)
        );
    }
}
