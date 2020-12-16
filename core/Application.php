<?php

namespace app\core;

use app\core\DB\DB;
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
    public DB $db;

    public function __construct(array $params) {
        $this->params = $params;
        $this->request = new Request();
        $this->db = new DB([
            'dbhost' => 'localhost',
            'dbname' => 'foodheven',
            'dbusername' => 'root',
            'dbpassword' => ''
        ]);

        self::$app = $this; // For easy access
    }
}
