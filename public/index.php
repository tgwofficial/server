<?php

require_once './config/database.php';

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;


$app = new \Slim\App(["settings" => $config]);

$app->get('/', function (Request $request, Response $response, array $args) {
    $response->getBody()->write("Atma Project API V1");

    return $response;
});

$container = $app->getContainer();

$container['db'] = function ($c) {
    $db = $c['settings']['db'];
    $pdo = new PDO("mysql:host=" . $db['host'] . ";dbname=" . $db['dbname'],
        $db['user'], $db['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $pdo;
};

$container['view'] = new \Slim\Views\PhpRenderer("./templates/");

$container['logger'] = function($c) {
    $logger = new \Monolog\Logger('my_logger');
    $file_handler = new \Monolog\Handler\StreamHandler("./logs/app.log");
    $logger->pushHandler($file_handler);
    return $logger;
};

$app->post('/api/push', function (Request $request, Response $response) {
    $updates = $request->getParsedBody();
    $this->logger->addInfo("Push Update: ".json_encode($updates));
    foreach ($updates as $update) {
        $updateEntity = new UpdateEntity($update);
        $updateMapper = new UpdateMapper($this->db);
        $updateMapper->save($updateEntity);
    }

    $response = $response->withJson(["success"=>true], 201);
    return $response;
});

$app->get('/api/pull', function (Request $request, Response $response) {
    $update_id = $request->getParam('update-id');
    $batch_size = $request->getParam('batch-size');
    $this->logger->addInfo("Pull Update: update-id=".$update_id."&batch-size=".$batch_size);
    $mapper = new UpdateMapper($this->db);
    $updates = $mapper->getBatchUpdates($update_id,$batch_size);

    $response = $response->withJson($updates);

    return $response;
});

$app->run();
