<?php

namespace App\Support;

final class SupportingDocumentTypes
{
    public const SCHOOL_ID = 'school_id';

    public const NATIONAL_ID = 'national_id';

    public const SIDE_FRONT = 'front';

    public const SIDE_BACK = 'back';

    /**
     * @return list<string>
     */
    public static function sides(): array
    {
        return [self::SIDE_FRONT, self::SIDE_BACK];
    }

    /**
     * @return list<string>
     */
    public static function allowed(): array
    {
        return [
            self::SCHOOL_ID,
            self::NATIONAL_ID,
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::SCHOOL_ID => 'School ID',
            self::NATIONAL_ID => 'PhilSys / National ID',
        ];
    }
}
