<?php

namespace App\Services;

final class KkProfilingValidationMessages
{
    public const INVALID_FULL_NAME = 'Invalid Full Name. The name on the uploaded School ID does not match the KK Profiling form. The first letter of your middle name must match the middle initial on the ID (e.g. form "Ana" → ID "A.").';

    public const INVALID_BARANGAY = 'Invalid Barangay. The uploaded School ID belongs to a different barangay.';

    public const DUPLICATE_IDENTITY = 'Duplicate registration detected. A Kabataan account with the same Full Name, Date of Birth, and Barangay already exists.';

    /** @deprecated Use DUPLICATE_IDENTITY */
    public const DUPLICATE_FULL_NAME = self::DUPLICATE_IDENTITY;

    public const OCR_READ_FAILED = 'Unable to read the uploaded School ID. Please upload a clearer front and back image.';
}
