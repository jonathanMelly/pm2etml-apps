<?php

namespace App\Constants;

class FileFormat
{
    //public const DEFAULT_DMZ_ASSET_PATH='dmz-assets';
    public const DMZ_ASSET_URL = 'asset';

    public const ATTACHMENT_DELETED_SUBFOLDER = 'deleted';

    public const ATTACHMENT_TEMPORARY_SUBFOLDER = 'pending';

    public const JOB_IMAGE_WIDTH = 350;

    public const JOB_IMAGE_HEIGHT = 350;

    public const JOB_IMAGE_TARGET_FORMAT = 'jpg';

    public const IMAGE_FORMATS = ['jpeg', 'jpg', 'svg', 'png', 'tiff', 'bmp'];

    public const JOB_ATTACHMENT_MAX_SIZE_IN_MO = 23;

    public const JOB_ATTACHMENT_MAX_COUNT = 10;

    public const JOB_DOC_ATTACHMENT_ALLOWED_EXTENSIONS =
        ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'md', 'zip', 'sql'];

    public const CONTRACT_EVALUATION_FORMATS = ['pdf'];

    public static function getImageFormatsAsRegex(): string
    {
        return self::getFileFormatAsRegex(self::IMAGE_FORMATS);
    }

    public static function getImageFormatsAsCSV(bool $withDot = false): string
    {
        return self::getFileFormatsAsCSV(self::IMAGE_FORMATS, $withDot);
    }

    public static function getFileFormatAsRegex(array $formats)
    {
        return implode('|', $formats);
    }

    public static function getFileFormatsAsCSV(array $formats, bool $withDot = false): string
    {
        return ($withDot ? '.' : '').implode(','.($withDot ? '.' : ''), $formats);
    }
}
