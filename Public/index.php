<?php
require __DIR__ . '/../vendor/autoload.php';

$app = Slim\Factory\AppFactory::create();

$app->get('/', function ($request, $response, $args) {
    $response->getBody()->write("¡Hola desde Slim en railway!..");
    return $response;
});

$app->run();
