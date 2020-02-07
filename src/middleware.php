<?php
use Tuupola\Middleware\HttpBasicAuthentication;
use Tuupola\Middleware\CorsMiddleware;

$container = $app->getContainer();
$container['logger'] = function($c) {
    $logger = new \Monolog\Logger('my_logger');
    $file_handler = new \Monolog\Handler\StreamHandler("../logs/app.log");
    $logger->pushHandler($file_handler);
    return $logger;
};

$container["jwt"] = function ($container) {
    return new StdClass;
};

$app->add(new \Slim\Middleware\Session([
    'name' => 'eftestapisession',
    'autorefresh' => true
]));

$app->add(new \Slim\Middleware\JwtAuthentication([
    "path" => "/",
    "logger" => $container['logger'],
    "secret" => "pOOGX1pmniVKdd6Nqbj5fZf0KWQh2SOr",
    "rules" => [
        new \Slim\Middleware\JwtAuthentication\RequestPathRule([
            "path" => "/",
            "passthrough" => [
                "/v1/employees/add",
                "/v1/employees/list",
                "/v1/employees/view",
                "/v1/employees/delete",
                "/v1/employees/update"
            ]
        ]),        
        new \Slim\Middleware\JwtAuthentication\RequestMethodRule([
            "passthrough" => ["OPTIONS"]
        ]),
    ],
    "callback" => function ($request, $response, $arguments) use ($container) {
        $container["jwt"] = $arguments["token"];
    },
    "error" => function ($request, $response, $arguments) {
        $data["status"] = "error";
        $data["message"] = $arguments["message"];
        return $response
            ->withHeader("Content-Type", "application/json")
            ->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    }
]));

$app->add(new \Slim\Middleware\HttpBasicAuthentication([
    "path" => "/api/token",
    "users" => [
        "user" => "password"
    ]
]));

if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');    // cache for 1 day
}

// Access-Control headers are received during OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");         

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

    exit(0);
}