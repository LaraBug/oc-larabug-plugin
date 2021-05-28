<?php return [
    'plugin' => [
        'name' => 'LaraBug',
        'description' => 'Official LaraBug plugin for October CMS',
    ],
    'fields' => [
        'use_custom_server' => 'Use custom LaraBug server',
        'sleep' => 'Set the sleep time between duplicate exceptions',
    ],
    'comments' => [
        'sleep' => 'This value is in seconds, default: 60 seconds (1 minute)',
    ],
];