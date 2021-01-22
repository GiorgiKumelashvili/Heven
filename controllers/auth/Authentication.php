<?php

/**
 * @noinspection
 *      SqlNoDataSourceInspection
 *      PhpUnreachableStatementInspection
 *
 * @example login:
 *
 *      "type": "login",
 *      "data": {
 *          "email": "g@exam.com",
 *          "password": "asdosdjo0sjdiopjsdj123123"
 *      }
 *
 * @example register
 *
 *      "type": "register",
 *      "data": {
 *          "username": "luka",
 *          "email": "luka@exam.com",
 *          "password": "luk32"
 *      }
 */

namespace app\controllers\auth;

use app\core\Application;
use Exception;
use Firebase\JWT\JWT;
use PDO;

class Authentication {
    private array $request;
    private array $data = [];
    private string $message = '';

    public function index() {
        // Set JSON header
        header("Content-Type: application/json");

        // Get post data
        $this->request = $this->getPostData();

        // Validation request
        $this->validatePostData();

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

        // If error exists
        if ($message) {
            $this->sendErrorData($message);
        }

        // Go to register or login
        if ($this->request['type'] === 'register') {
            $this->register($this->request['data']);
        }
        else {
            $this->login($this->request['data']);
        }
    }

    /**
     * @param array $data
     * @return void
     *
     * Main register method !!!
     */
    private function register(array $data): void {
        $username = filter_var($data['username'], FILTER_SANITIZE_STRING);
        $email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
        $password = $data['password'];
        $db = Application::$app->db->connection();

        // Check user existence [email]
        $userData = $this->returnUserData($email);

        // Inserting user credentials into DB
        if (!$userData) {
            // Inserting
            $stmt = $db->prepare("
                INSERT INTO users
                    (username, email, password, identifier)
                VALUES
                    (:username, :email, :pwd, :identifier)
            ");

            try {
                $stmt->execute([
                    ':username' => $username,
                    ':email' => $email,
                    ':pwd' => password_hash($password, PASSWORD_DEFAULT),
                    ':identifier' => bin2hex(openssl_random_pseudo_bytes(50))
                ]);
            }
            catch (Exception $e) {
                $this->sendErrorData($e->getMessage());
            }


            $this->message = "User succesfuly registered";
        }
        else {
            $this->sendErrorData("Username already exists");
        }
    }

    /**
     * @param array $data
     * @return void
     *
     * Main login method !!!
     */
    private function login(array $data): void {
        $email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);

        // Check user existence [email]
        $userData = $this->returnUserData($email);

        // Verifying user credentials
        if ($userData && password_verify($data['password'], $userData['password'])) {
            $this->message = "User authentications successs";
            $this->data['accessToken'] = TokenController::generateToken($userData, 'access');
            $this->data['refreshToken'] = TokenController::generateToken($userData, 'refresh');
            $this->data['identifier'] = $userData['identifier'];
        }
        else {
            $this->sendErrorData("Incorrect email or password");
        }
    }

    private function returnUserData(string $email) {
        $userEmail = filter_var($email, FILTER_SANITIZE_EMAIL);
        $db = Application::$app->db->connection();

        // Check user existence [email]
        $stmt = $db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute([':email' => $userEmail]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function sendPostData(): void {
        $response = [];

        if ($this->message) $response['message'] = $this->message;
        if ($this->data) $response = array_merge($response, $this->data);
        Application::$app->response->sendResponse('success', $response);
    }

    private function sendErrorData(string $message): void {
        Application::$app->response->sendResponse('error', $message);
    }

    private function returnUserDataByIdentifier(array $postData): bool {
        try {
            $accessKey = $_ENV['REFRESH_TOKEN_SECRET'];
            $refreshToken = $postData['refreshToken'];
            $payload = JWT::decode($refreshToken, $accessKey, ['HS256']);
            $payload = json_decode(json_encode($payload), true);
            $identifier = $payload['identifier'];
            $db = Application::$app->db->connection();


            $stmt = $db->prepare("SELECT identifier FROM users WHERE identifier = :identifier");
            $stmt->execute([':identifier' => $identifier]);
            $identifierArr = $stmt->fetchAll(PDO::FETCH_COLUMN);

            return $identifierArr[0] === $identifier;
        }
        catch (Exception $e) {
            $this->sendErrorData('Something went wrong with token');
        }
    }

    public function isUserAuthenticated(): void {
        $postData = Application::$app->request->getPostData();

        if (count($postData) === 0)
            $this->sendErrorData('Empty object');

        $doesUserExists = $this->returnUserDataByIdentifier($postData);
        $condition = $doesUserExists ? 'success' : 'error';

        Application::$app->response->sendResponse($condition, [
            'UserExists' => $doesUserExists
        ]);
    }
}