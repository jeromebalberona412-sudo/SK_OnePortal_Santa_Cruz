<?php

namespace App\Services;

final class KkProfilingValidationMessages
{
    public const DUPLICATE_IDENTITY = 'Duplicate registration detected. A Kabataan account with the same Full Name, Date of Birth, and Barangay already exists.';

    /** @deprecated Use DUPLICATE_IDENTITY */
    public const DUPLICATE_FULL_NAME = self::DUPLICATE_IDENTITY;

    public static function ocrFrontReadFailed(): string
    {
        return 'Could not read the name on the front of your School ID. Please re-upload a clear photo of the front side.';
    }

    public static function ocrBackReadFailed(): string
    {
        return 'Could not read the address on the back of your School ID. Please re-upload a clear photo of the back side showing your address.';
    }

    public static function uploadProcessingFailed(): string
    {
        return 'Your School ID files could not be processed. Please re-upload both the front and back images.';
    }

    public static function nameMismatch(string $formFullName, string $idFullName): string
    {
        $formFullName = trim($formFullName) !== '' ? trim($formFullName) : '(not provided)';
        $idFullName = trim($idFullName) !== '' ? trim($idFullName) : '(unreadable)';

        return 'Full name mismatch. KK Profiling Form: '.$formFullName.'. School ID (front): '.$idFullName.'.';
    }

    public static function addressMismatch(string $formAddress, string $idAddress): string
    {
        $formAddress = trim($formAddress) !== '' ? trim($formAddress) : '(not provided)';
        $idAddress = trim($idAddress) !== '' ? trim($idAddress) : '(unreadable)';

        return 'Address mismatch. KK Profiling Form: '.$formAddress.'. School ID (back): '.$idAddress.'.';
    }

    /**
     * @param  array<string, mixed>  $fields
     */
    public static function formAddressLabel(array $fields, ?string $barangayName = null): string
    {
        $parts = array_values(array_filter([
            trim((string) ($fields['purok_zone'] ?? '')),
            trim((string) ($barangayName ?? '')),
            'Santa Cruz',
            'Laguna',
        ], fn (string $part) => $part !== ''));

        return $parts !== [] ? implode(', ', $parts) : '(not provided)';
    }
}
