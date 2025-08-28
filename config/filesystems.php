<?php

return [
    'default' => 'local',
    'disks' => [
        'local' => [
            'driver' => 'local',
            'root' => getcwd(),
        ],
        'backup' => [
            'driver' => 'local',
            'root' => getcwd().'/backup',
        ],
    ],
];
