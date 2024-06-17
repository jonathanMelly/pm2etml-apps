<?php

return [

    'disks' => [
        \App\Constants\DiskNames::UPLOAD => [
            'driver' => 'local',
            'root' => storage_path('app'.DIRECTORY_SEPARATOR.'uploads'),
        ],
    ],

];
