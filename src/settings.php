<?php
return [
    'settings' => [
        'displayErrorDetails' => true, // set to false in production
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header

        // Renderer settings
        'renderer' => [
            'template_path' => __DIR__ . '/../templates/',
        ],

        // Monolog settings
        'logger' => [
            'name' => 'slim-app',
            'path' => __DIR__ . '/../logs/app.log',
            'level' => \Monolog\Logger::DEBUG,
        ],

        'db' => [
            // 'servername' =>'eb360.cxeb2rsmch5m.ap-southeast-2.rds.amazonaws.com',
            'servername' =>'localhost:8889',
            'username' => 'root',
            'password' => 'root',
            'dbname' => 'fe-test',
            'port' => '3306'
        ],
        'debug' => true,
        'displayErrorDetails' => true,
        'determineRouteBeforeAppMiddleware' => true    
    ],
];
