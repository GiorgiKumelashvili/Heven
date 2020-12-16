<?php

require_once "../vendor/autoload.php";

//Namespaces
use app\core\Application;
use app\core\Routing\Api;

// Configurations later use it from configs folder
$configs = [
    'db' => 123
];

// Main app controller
$app = new Application($configs);

Api::get("/", ["xxxxsss", 'indexssss']);

Api::get("/db", [\app\controllers\DBcontroller::class , 'showDB']);
Api::get("/db/createtables", [\app\controllers\DBcontroller::class , 'createTables']);
Api::get("/db/empty", [\app\controllers\DBcontroller::class , 'emptyDB']);

Api::post("/", ["root post", 'index']);
Api::post("/xxx", ["postx", 'index']);
Api::post("/yyy", ["posty", 'index']);

Api::validateUnkownUrl();
