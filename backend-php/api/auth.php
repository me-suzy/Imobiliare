<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';
require_once '../vendor/autoload.php'; // Pentru JWT

use \Firebase\JWT\JWT;

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];
$request_uri = explode('/', trim($_SERVER['REQUEST_URI'],'/'));
$action = end($request_uri);

if ($method == 'POST') {
    $data = json_decode(file_get_contents("php://input"));

    if ($action == 'register') {
        // Registration logic
        if (!empty($data->email) && !empty($data->password) && !empty($data->name)) {
            // Check if user exists
            $query = "SELECT id FROM users WHERE email = :email";
            $stmt = $db->prepare($query);
            $stmt->bindParam(":email", $data->email);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                http_response_code(400);
                echo json_encode(array("error" => "Emailul există deja"));
                exit();
            }

            // Create user
            $query = "INSERT INTO users (name, email, password, phone, created_at) 
                      VALUES (:name, :email, :password, :phone, NOW())";
            
            $stmt = $db->prepare($query);
            
            $password_hash = password_hash($data->password, PASSWORD_BCRYPT);
            
            $stmt->bindParam(":name", $data->name);
            $stmt->bindParam(":email", $data->email);
            $stmt->bindParam(":password", $password_hash);
            $stmt->bindParam(":phone", $data->phone);

            if ($stmt->execute()) {
                $user_id = $db->lastInsertId();
                
                // Generate JWT
                $secret_key = "YOUR_SECRET_KEY_HERE";
                $issued_at = time();
                $expiration_time = $issued_at + (7 * 24 * 60 * 60); // 7 days
                
                $token = array(
                    "iat" => $issued_at,
                    "exp" => $expiration_time,
                    "data" => array(
                        "id" => $user_id,
                        "email" => $data->email
                    )
                );

                $jwt = JWT::encode($token, $secret_key, 'HS256');

                http_response_code(201);
                echo json_encode(array(
                    "message" => "Utilizator creat cu succes",
                    "token" => $jwt
                ));
            } else {
                http_response_code(503);
                echo json_encode(array("error" => "Eroare la crearea utilizatorului"));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("error" => "Date incomplete"));
        }
    }

    elseif ($action == 'login') {
        // Login logic
        if (!empty($data->email) && !empty($data->password)) {
            $query = "SELECT id, name, email, password FROM users WHERE email = :email";
            $stmt = $db->prepare($query);
            $stmt->bindParam(":email", $data->email);
            $stmt->execute();

            $num = $stmt->rowCount();

            if ($num > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);

                if (password_verify($data->password, $row['password'])) {
                    $secret_key = "YOUR_SECRET_KEY_HERE";
                    $issued_at = time();
                    $expiration_time = $issued_at + (7 * 24 * 60 * 60);
                    
                    $token = array(
                        "iat" => $issued_at,
                        "exp" => $expiration_time,
                        "data" => array(
                            "id" => $row['id'],
                            "email" => $row['email']
                        )
                    );

                    $jwt = JWT::encode($token, $secret_key, 'HS256');

                    http_response_code(200);
                    echo json_encode(array(
                        "message" => "Autentificare reușită",
                        "token" => $jwt,
                        "user" => array(
                            "id" => $row['id'],
                            "name" => $row['name'],
                            "email" => $row['email']
                        )
                    ));
                } else {
                    http_response_code(401);
                    echo json_encode(array("error" => "Email sau parolă incorectă"));
                }
            } else {
                http_response_code(401);
                echo json_encode(array("error" => "Email sau parolă incorectă"));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("error" => "Date incomplete"));
        }
    }
}
?>


