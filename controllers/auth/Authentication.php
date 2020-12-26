<?php

/**
 * @noinspection
 *      SqlNoDataSourceInspection
 *      PhpUnreachableStatementInspection
 */

namespace app\controllers\auth;

use app\core\Application;
use app\core\Routing\Api;
use PDO;

class Authentication {
    public array $request;
    public string $response;

    public function index() {
        // Set JSON header
        header("Content-Type: application/json");

        // Get post dat
        $this->request = $this->getPostData();

        // Validation request
        $this->validatePostData();

        // Main validation
        $this->validateData();

        // Final send
        $this->sendPostData();
    }

    private function getPostData() {
        $req = stream_get_contents(fopen('php://input', 'r'));
        return json_decode($req, true);
    }

    private function validatePostData() {
        $req = $this->request;
        $message = '';

        if (empty($req)) {
            $message = "Data mustn't be empty should contain [type],[data] props/key";
        }
        elseif (!array_key_exists('type', $req)) {
            $message = "Data should have property [type]";
        }
        elseif (!array_key_exists('data', $req)) {
            $message = "Data should have property [data]";
        }
        else {
            // type and data check according to value of type
            $data = $req['data'];
            $keys = ['email', 'password'];

            if ($req['type'] === 'register') {
                $keys = ['username', ...$keys];

                foreach ($keys as $key) {
                    if (!array_key_exists($key, $data)) {
                        $message = "Key [data] should have property [{$key}]";
                        break;
                    }
                }
            }
            elseif ($req['type'] === 'login') {
                foreach ($keys as $key) {
                    if (!array_key_exists($key, $data)) {
                        $message = "Key [data] should have property [{$key}]";
                        break;
                    }
                }

                if (array_key_exists('username', $data)) {
                    $message = "Username inside Key [data] is unnecessary";
                }
            }
            else {
                $message = "Key [type] must be either login or register";
            }
        }

        if ($message) self::throwException($message);
    }

    private function validateData() {
        if ($this->request['type'] === 'register') {
            $this->register($this->request['data']);
        }
        else {
            $this->login($this->request['data']);
        }
    }

    private function register(array $data): void {
        $username = filter_var($data['username'], FILTER_SANITIZE_STRING);
        $email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
        $password = $data['password'];

        $db = Application::$app->db->connection();

        // Check user existence [email]
        $stmt = $db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $userExists = $stmt->fetchColumn();

        // Inserting user credentials into DB
        if (!$userExists) {
            // Inserting
            $stmt = $db->prepare("
                INSERT INTO users
                    (username, email, password)
                VALUES
                    (:username, :email, :pwd)
            ");

            $stmt->execute([
                ':username' => $username,
                ':email' => $email,
                ':pwd' => password_hash($password, PASSWORD_DEFAULT)
            ]);

            $this->response = "User succesfuly registered";
        }
        else {
            self::throwException("Username already exists");
        }
    }

    private function login(array $data): void {
        $email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
        $password = $data['password'];

        $db = Application::$app->db->connection();

        // Check user existence [email]
        $stmt = $db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verifying user credentials
        if ($data && password_verify($password, $data['password'])) {
            $this->response = "User authentications successs";
        }
        else {
            self::throwException("Incorrect email or password");
        }
    }

    private function sendPostData(): void {
        echo json_encode([
            "statuscode" => 1,
            "message" => $this->response
        ]);
    }

    private static function throwException(string $message): void {
        Api::throwException(json_encode([
            "statuscode" => 0,
            "error" => $message
        ]));
    }
}