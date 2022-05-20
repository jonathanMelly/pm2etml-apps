<?php

namespace App\Constants;

class FileFormat
{
    public const JOB_IMAGE_WIDTH  = 350;
    public const JOB_IMAGE_HEIGHT = 350;
    public const JOB_IMAGE_TARGET_FORMAT = 'jpg';
    public const IMAGE_FORMATS=['jpeg','jpg','svg','png','tiff','bmp'];

    public static function getImageFormatsAsRegex(): string
    {
        return implode('|', self::IMAGE_FORMATS);
    }

    public static function getImageFormatsAsCSV(): string
    {
        return implode(',', self::IMAGE_FORMATS);
    }
}
