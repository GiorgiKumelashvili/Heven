<?php

use app\controllers\auth\Authentication;
use app\controllers\Temp;

$routes = [
    "get" => [
        "/" => [Temp::class, 'index'],
    ],
    "post" => [
        // Authentication
        "/auth"=> [Authentication::class, 'index'],

        // random routes
        "/" =>["root post", 'index'],
        "/xxx" => ["postx", 'index'],
        "/yyy" => ["posty", 'index'],
    ]
];