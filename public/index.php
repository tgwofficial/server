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
    if($updates==null){
        $this->logger->addInfo("Push Update: error, updates is null");
        return $response->withJson(["error"=>"updates is null"], 400);
    }
    if(key($updates)!='0'){
        $this->logger->addInfo("Push Update: error, updates is not array");
        return $response->withJson(["error"=>"updates is not array"], 400);
    }
    $keys = ["update_id","form_name","data","location_id","user_id"];
    foreach ($keys as $key) {
        if(!array_key_exists($key,$updates[0])){
            $this->logger->addInfo("Push Update: error, $key is missing");
            return $response->withJson(["error"=>"$key is missing"], 400);
        }
    }
    $this->logger->addInfo("Push Update: ".json_encode($updates));
    foreach ($updates as $update) {
        $updateEntity = new UpdateEntity($update);
        $updateMapper = new UpdateMapper($this->db);
        $dataMapper = new DataMapper($this->db);
        $updateMapper->save($updateEntity);
        $dataMapper->save($update['form_name'],$update['data'][0]);
    }

    $response = $response->withJson(["success"=>"true"], 201);
    return $response;
});

$app->get('/api/pull', function (Request $request, Response $response) {
    $update_id = $request->getParam('update-id');
    $batch_size = $request->getParam('batch-size');
    $location_id = $request->getParam('location-id');
    if($update_id==""||$batch_size==""||$location_id==""){
        $this->logger->addInfo("Pull Update: Error request");
        return $response->withStatus(400);
    }
    $this->logger->addInfo("Pull Update: location-id=".$location_id."&update-id=".$update_id."&batch-size=".$batch_size);
    $mapper = new UpdateMapper($this->db);
    $updates = $mapper->getBatchUpdatesByLocationId($location_id,$update_id,$batch_size);
    $response = $response->withJson($updates);

    return $response;
});

$app->post('/api/location/create', function (Request $request, Response $response) {
    $locs = $request->getParsedBody();
    if($locs==null){
        $this->logger->addInfo("Push Update: error, locs is null");
        return $response->withJson(["error"=>"locs is null"], 400);
    }
    $keys = ["name","parent_location","location_tag_id"];
    foreach ($keys as $key) {
        if(!array_key_exists($key,$locs)){
            $this->logger->addInfo("Push Update: error, $key is missing");
            return $response->withJson(["error"=>"$key is missing"], 400);
        }
    }
    $this->logger->addInfo("Push Update: ".json_encode($locs));
    $locEntity = new LocationEntity($locs);
    $locMapper = new LocationMapper($this->db);
    $locMapper->save($locEntity);

    $response = $response->withJson(["success"=>"true"], 201);


    return $response;
});

$app->get('/api/locations', function (Request $request, Response $response) {
    $this->logger->addInfo("Get All Location");
    $mapper = new LocationMapper($this->db);
    $locations = $mapper->getLocations();
    $response = $response->withJson($locations);

    return $response;
});

$app->get('/api/location', function (Request $request, Response $response) {
    $location_id = $request->getParam('location-id');
    if($location_id==""){
        $this->logger->addInfo("Get Location: Error request");
        return $response->withStatus(400);
    }
    $this->logger->addInfo("Get Location: location-id=".$location_id);
    $mapper = new LocationMapper($this->db);
    $location = $mapper->getLocationById($location_id);
    $response = $response->withJson($location);

    return $response;
});


$app->post('/api/user/create', function (Request $request, Response $response) {
    $response->getBody()->write("Atma Project API V1 - Create User");

    return $response;
});

$app->get('/api/user', function (Request $request, Response $response) {
    $response->getBody()->write("Atma Project API V1 - Get User");

    return $response;
});

$app->get('/api/users', function (Request $request, Response $response) {
    $response->getBody()->write("Atma Project API V1 - Get All Users");

    return $response;
});


$app->post('/api/auth/login', function (Request $request, Response $response) {
    $credential = $request->getParsedBody();
    if($credential==null){
        $this->logger->addInfo("Login: error, credential is null");
        return $response->withJson(["error"=>"credential is null"], 400);
    }
    $keys = ["username","password"];
    foreach ($keys as $key) {
        if(!array_key_exists($key,$credential)){
            $this->logger->addInfo("Login: error, $key is missing");
            return $response->withJson(["error"=>"$key is missing"], 400);
        }
    }
    $postdata = http_build_query(
        array(
            'identity' => $credential['username'],
            'password' => $credential['password']
        )
    );

    $opts = array('http' =>
        array(
            'method'  => 'POST',
            'header'  => 'Content-type: application/x-www-form-urlencoded',
            'content' => $postdata
        )
    );

    $context  = stream_context_create($opts);
    $logged = file_get_contents('http://ard.theseforall.org/auth/login_api', false, $context)=='success'?true:false;
    if($logged){
        $mapper = new LoginMapper($this->db);
        $info = $mapper->getLoginInfo($credential['username']);
        $response = $response->withJson($info);
    }else{
        return $response->withStatus(401);
    }

    return $response;
});

$app->get('/api/auth/login', function (Request $request, Response $response) {
    $username = $request->getParam('username');
    $password = $request->getParam('password');
    if($username==""||$password==""){
        $this->logger->addInfo("Login: Error request, username or password is empty");
        return $response->withJson(["error"=>"username or password is empty"], 400);
    }
    $credential['username'] = $username;
    $credential['password'] = $password;
    if($credential==null){
        $this->logger->addInfo("Login: error, credential is null");
        return $response->withJson(["error"=>"credential is null"], 400);
    }
    $keys = ["username","password"];
    foreach ($keys as $key) {
        if(!array_key_exists($key,$credential)){
            $this->logger->addInfo("Login: error, $key is missing");
            return $response->withJson(["error"=>"$key is missing"], 400);
        }
    }
    $postdata = http_build_query(
        array(
            'identity' => $credential['username'],
            'password' => $credential['password']
        )
    );

    $opts = array('http' =>
        array(
            'method'  => 'POST',
            'header'  => 'Content-type: application/x-www-form-urlencoded',
            'content' => $postdata
        )
    );

    $context  = stream_context_create($opts);
    $logged = file_get_contents('http://ard.theseforall.org/auth/login_api', false, $context)=='success'?true:false;
    if($logged){
        $mapper = new LoginMapper($this->db);
        $info = $mapper->getLoginInfo($credential['username']);
        $response = $response->withJson($info);
    }else{
        return $response->withStatus(401);
    }

    return $response;
});

$app->run();
