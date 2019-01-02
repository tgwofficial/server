<?php

require_once './config/database.php';

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;


$app = new \Slim\App(["settings" => $config]);

$app->get('/', function (Request $request, Response $response, array $args) {
    $response->withHeader('Access-Control-Allow-Origin', '*')->getBody()->write("Atma Project API V1");

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
        return $response->withHeader('Access-Control-Allow-Origin', '*')->withJson(["error"=>"updates is null"], 400);
    }
    if(key($updates)!='0'){
        $this->logger->addInfo("Push Update: error, updates is not array");
        return $response->withHeader('Access-Control-Allow-Origin', '*')->withJson(["error"=>"updates is not array"], 400);
    }
    $keys = ["update_id","form_name","data","desa","dusun","user_id"];
    foreach ($keys as $key) {
        if(!array_key_exists($key,$updates[0])){
            $this->logger->addInfo("Push Update: error, $key is missing");
            return $response->withHeader('Access-Control-Allow-Origin', '*')->withJson(["error"=>"$key is missing"], 400);
        }
    }
    $this->logger->addInfo("Push Update: ".json_encode($updates));
    foreach ($updates as $update) {
        $data = json_decode($update['data'],TRUE);
        $updateEntity = new UpdateEntity($update);
        $updateMapper = new UpdateMapper($this->db);
        $dataMapper = new DataMapper($this->db);
        $updateMapper->save($updateEntity);
        $data['user_id'] = $updateEntity->getUserId();
        $data['location_id'] = $updateEntity->getLocationId();
        $data['update_id'] = $updateEntity->getUpdateId();
        $data['timestamp'] = date("Y-m-d H:i:s",$updateEntity->getUpdateId());
        $dataMapper->save($update['form_name'],$data);
    }

    $response = $response->withHeader('Access-Control-Allow-Origin', '*')->withJson(["success"=>"true"], 201);
    return $response;
});

$app->get('/api/pull', function (Request $request, Response $response) {
    $update_id = $request->getParam('update-id');
    $batch_size = $request->getParam('batch-size');
    $desa = $request->getParam('desa');
    $dusun = $request->getParam('dusun');
    if($update_id==""||$batch_size==""){
        $this->logger->addInfo("Pull Update: Error request");
        return $response->withHeader('Access-Control-Allow-Origin', '*')->withStatus(400);
    }
    if($desa==""&&$dusun==""){
        $this->logger->addInfo("Pull Update: Error request");
        return $response->withHeader('Access-Control-Allow-Origin', '*')->withStatus(400);
    }
    $mapper = new UpdateMapper($this->db);
    if ($desa!="") {
        $this->logger->addInfo("Pull Update: desa=".$desa."&update-id=".$update_id."&batch-size=".$batch_size);
        $updates = $mapper->getBatchUpdatesByDesa($desa,$update_id,$batch_size);
        $response = $response->withHeader('Access-Control-Allow-Origin', '*')->withJson($updates);

        return $response;
    }elseif ($dusun!="") {
        $this->logger->addInfo("Pull Update: dusun=".$dusun."&update-id=".$update_id."&batch-size=".$batch_size);
        $updates = $mapper->getBatchUpdatesByDusun($dusun,$update_id,$batch_size);
        $response = $response->withHeader('Access-Control-Allow-Origin', '*')->withJson($updates);

        return $response;
    }
    
});

$app->post('/api/location/create', function (Request $request, Response $response) {
    $locs = $request->getParsedBody();
    if($locs==null){
        $this->logger->addInfo("Push Update: error, locs is null");
        return $response->withHeader('Access-Control-Allow-Origin', '*')->withJson(["error"=>"locs is null"], 400);
    }
    $keys = ["name","parent_location","location_tag_id"];
    foreach ($keys as $key) {
        if(!array_key_exists($key,$locs)){
            $this->logger->addInfo("Push Update: error, $key is missing");
            return $response->withHeader('Access-Control-Allow-Origin', '*')->withJson(["error"=>"$key is missing"], 400);
        }
    }
    $this->logger->addInfo("Push Update: ".json_encode($locs));
    $locEntity = new LocationEntity($locs);
    $locMapper = new LocationMapper($this->db);
    $locMapper->save($locEntity);

    $response = $response->withHeader('Access-Control-Allow-Origin', '*')->withJson(["success"=>"true"], 201);


    return $response;
});

$app->get('/api/locations', function (Request $request, Response $response) {
    $this->logger->addInfo("Get All Location");
    $mapper = new LocationMapper($this->db);
    $locations = $mapper->getLocations();
    $response = $response->withHeader('Access-Control-Allow-Origin', '*')->withJson($locations);

    return $response;
});

$app->get('/api/location', function (Request $request, Response $response) {
    $location_id = $request->getParam('location-id');
    if($location_id==""){
        $this->logger->addInfo("Get Location: Error request");
        return $response->withHeader('Access-Control-Allow-Origin', '*')->withStatus(400);
    }
    $this->logger->addInfo("Get Location: location-id=".$location_id);
    $mapper = new LocationMapper($this->db);
    $location = $mapper->getLocationById($location_id);
    $response = $response->withHeader('Access-Control-Allow-Origin', '*')->withJson($location);

    return $response;
});

$app->get('/api/location/child', function (Request $request, Response $response) {
    $location_id = $request->getParam('location-id');
    if($location_id==""){
        $this->logger->addInfo("Get Child Location: Error request");
        return $response->withHeader('Access-Control-Allow-Origin', '*')->withStatus(400);
    }
    $this->logger->addInfo("Get Child Locations: location-id=".$location_id);
    $mapper = new LocationMapper($this->db);
    $location = $mapper->getChildLocationById($location_id);
    $response = $response->withHeader('Access-Control-Allow-Origin', '*')->withJson($location);

    return $response;
});

$app->get('/api/data/reports', function (Request $request, Response $response) {
    $location_id = $request->getParam('location-id');
    $thn = $request->getParam('t');
    $bln = $request->getParam('b');
    if($location_id==""){
        $this->logger->addInfo("Get Data Reports: Error request");
        return $response->withHeader('Access-Control-Allow-Origin', '*')->withStatus(400);
    }
    $this->logger->addInfo("Get Data Reports: location-id=".$location_id);
    $mapper = new LocationMapper($this->db);
    $dataMapper = new DataMapper($this->db);
    $location = $mapper->getLocationById($location_id);
    if ($bln=="99") {
        $location['data'] = $dataMapper->getYearlyReports($location,$thn,$bln);
    }else{
        $location['data'] = $dataMapper->getReports($location,$thn,$bln);
    }

    $response = $response->withHeader('Access-Control-Allow-Origin', '*')->withJson($location);

    return $response;
});

$app->get('/api/data/reports/child', function (Request $request, Response $response) {
    $location_id = $request->getParam('location-id');
    $thn = $request->getParam('t');
    $bln = $request->getParam('b');
    if($location_id==""){
        $this->logger->addInfo("Get Data Reports: Error request");
        return $response->withHeader('Access-Control-Allow-Origin', '*')->withStatus(400);
    }
    $this->logger->addInfo("Get Data Reports: location-id=".$location_id);
    $mapper = new LocationMapper($this->db);
    $dataMapper = new DataMapper($this->db);
    $location = $mapper->getChildLocationById($location_id);
    foreach ($location as $key => $loc) {
         $location[$key]['data'] = $dataMapper->getReports($loc,$thn,$bln);
    }

    $response = $response->withHeader('Access-Control-Allow-Origin', '*')->withJson($location);

    return $response;
});


$app->post('/api/user/create', function (Request $request, Response $response) {
    $response->withHeader('Access-Control-Allow-Origin', '*')->getBody()->write("Atma Project API V1 - Create User");

    return $response;
});

$app->get('/api/user', function (Request $request, Response $response) {
    $response->withHeader('Access-Control-Allow-Origin', '*')->getBody()->write("Atma Project API V1 - Get User");

    return $response;
});

$app->get('/api/users', function (Request $request, Response $response) {
    $response->withHeader('Access-Control-Allow-Origin', '*')->getBody()->write("Atma Project API V1 - Get All Users");

    return $response;
});


$app->post('/api/auth/login', function (Request $request, Response $response) {
    $credential = $request->getParsedBody();
    if($credential==null){
        $this->logger->addInfo("Login: error, credential is null");
        return $response->withHeader('Access-Control-Allow-Origin', '*')->withJson(["error"=>"credential is null"], 400);
    }
    $keys = ["username","password"];
    foreach ($keys as $key) {
        if(!array_key_exists($key,$credential)){
            $this->logger->addInfo("Login: error, $key is missing");
            return $response->withHeader('Access-Control-Allow-Origin', '*')->withJson(["error"=>"$key is missing"], 400);
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
        $response = $response->withHeader('Access-Control-Allow-Origin', '*')->withJson($info);
    }else{
        return $response->withHeader('Access-Control-Allow-Origin', '*')->withStatus(401);
    }

    return $response;
});

$app->get('/api/auth/login', function (Request $request, Response $response) {
    $username = $request->getParam('username');
    $password = $request->getParam('password');
    if($username==""||$password==""){
        $this->logger->addInfo("Login: Error request, username or password is empty");
        return $response->withHeader('Access-Control-Allow-Origin', '*')->withJson(["error"=>"username or password is empty"], 400);
    }
    $credential['username'] = $username;
    $credential['password'] = $password;
    if($credential==null){
        $this->logger->addInfo("Login: error, credential is null");
        return $response->withHeader('Access-Control-Allow-Origin', '*')->withJson(["error"=>"credential is null"], 400);
    }
    $keys = ["username","password"];
    foreach ($keys as $key) {
        if(!array_key_exists($key,$credential)){
            $this->logger->addInfo("Login: error, $key is missing");
            return $response->withHeader('Access-Control-Allow-Origin', '*')->withJson(["error"=>"$key is missing"], 400);
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
        $response = $response->withHeader('Access-Control-Allow-Origin', '*')->withJson($info);
    }else{
        return $response->withHeader('Access-Control-Allow-Origin', '*')->withStatus(401);
    }

    return $response;
});

$app->run();
