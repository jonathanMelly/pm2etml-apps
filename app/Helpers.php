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
        return storage_path('dmz-assets/'.$file??'');
    }
}

if(!function_exists('tbl'))
{
    function tbl($class)
    {
        return app($class)->getTable();
    }
}

