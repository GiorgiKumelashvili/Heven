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

Api::get("/", ["classname", 'index']);
Api::get("/xxx", ["xxxx", 'index']);

Api::post("/", ["root post", 'index']);
Api::post("/xxx", ["postx", 'index']);
Api::post("/yyy", ["posty", 'index']);

Api::validateUnkownUrl();
