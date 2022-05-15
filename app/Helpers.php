<?php

//currently empty
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
if(!function_exists('dmzImgUrl'))
{
    function dmzImgUrl(?string $file):string
    {
        return '/dmz-assets/'.$file??'';
    }
}

if(!function_exists('dmzStoragePath'))
{
    function dmzStoragePath(?string $file=''):string
    {
        return storage_path('dmz-assets'.DIRECTORY_SEPARATOR.$file??'');
    }
}

if(!function_exists('tbl'))
{
    function tbl($class)
    {
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

