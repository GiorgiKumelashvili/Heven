<?php

use app\controllers\auth\Authentication;
use app\controllers\auth\TokenController;
use app\controllers\Temp;

$ApiRoutes = [
    "get" => [
        "/" => [Temp::class, 'index'],
    ],

    "post" => [
        // Authentication login/register
        "/auth" => [Authentication::class, 'index'],

        // Access token refresh
        "/refreshtoken" => [TokenController::class, 'refreshAccessToken'],

        // Route for checking if user is authenticated
        "/1" => [Authentication::class, 'isUserAuthenticated'],

    ]
];