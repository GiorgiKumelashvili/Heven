<?php

namespace app\controllers;

class Temp {
    public function index() {
        header('Content-Type: application/json');

        $arr= [
            'message' => 'hello api user this is root repository'
        ];

        echo json_encode($arr);
    }
}