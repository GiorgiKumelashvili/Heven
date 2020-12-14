<?php

namespace app\core;

use app\core\Routing\Request;

/**
 * Class Application
 * @package app\core
 * @param Request $request
 */
class Application {
    public static Application $app;
    public array $params;
    public Request $request;

    public function __construct(array $params) {
        $this->params = $params;
        $this->request = new Request();
        self::$app = $this; // For easy access
    }
}
