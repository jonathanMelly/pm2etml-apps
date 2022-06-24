<?php

//currently empty
use App\Models\Attachment;

if(!function_exists('ordinal'))
{
    function ordinal(int $number):string
    {
        if($number>31)
        {
            return '';
        }
        return preg_replace('/\d+/','',date("S", mktime(0, 0, 0, 0, $number, 0)));
    }
}

if(!function_exists('uploadDisk'))
{
    function uploadDisk(): \Illuminate\Contracts\Filesystem\Filesystem|\Illuminate\Filesystem\FilesystemAdapter
    {
        return Storage::disk(\App\Constants\DiskNames::UPLOAD);
    }
}

if(!function_exists('attachmentPathInUploadDisk'))
{
    function attachmentPathInUploadDisk(?string $file=null, bool $temporary=false, bool $deleted=false):string
    {
        $parts = [];
        if($temporary)
        {
            $parts[]=\App\Constants\FileFormat::ATTACHMENT_TEMPORARY_SUBFOLDER;
        }
        if($deleted)
        {
            $parts[]=\App\Constants\FileFormat::ATTACHMENT_DELETED_SUBFOLDER;
        }
        if($file!=null)
        {
            $parts[]=$file;
        }
        return implode(DIRECTORY_SEPARATOR,$parts);
    }
}

if(!function_exists('attachmentUri'))
{
    function attachmentUri(Attachment $attachment):string
    {
        return route('dmz-asset',['file'=>$attachment->storage_path]);
    }
}

if(!function_exists('tbl'))
{
    function tbl($class)
    {
        //TODO add cache ?
        return app($class)->getTable();
    }
}

if(!function_exists('b2s'))
{
    function b2s(mixed $boolValue):string
    {
        if(!is_bool($boolValue))
        {
            $boolValue = filter_var($boolValue,FILTER_VALIDATE_BOOLEAN);

        }
        return $boolValue?'true':'false';
    }
}

if(!function_exists('existsAndNotEmpty'))
{
    function existsAndNotEmpty(ArrayAccess $array,mixed $key)
    {
        if(isset($array[$key]) && ($value=trim($array[$key]))!=='')
        {
            return $value;
        }
        return null;
    }
}

if(!function_exists('stringNullOrEmpty'))
{
    function stringNullOrEmpty(?string $str): bool
    {
        return $str===null || trim($str)==='';
    }
}

