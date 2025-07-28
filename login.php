<?php
require 'vendor/autoload.php';
use \Firebase\JWT\JWT;

include 'config.php';

$key = "your_secret_key"; // Ganti dengan kunci rahasia
$issuedAt = time();
$expirationTime = $issuedAt + (3600 * 24 * 1); // token berlaku selama 1 hari
// $expirationTime = $issuedAt + (300); // token berlaku selama 5 menit
$issuer = "https://sibeux.my.id";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Cek pengguna di database
    if ($stmt = $db->prepare("SELECT * FROM user WHERE username = ?")) {
        
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            $payload = [
                'iat' => $issuedAt,
                'exp' => $expirationTime,
                'iss' => $issuer,
                'data' => [
                    'username' => $user['username'],
                ],
            ];

            $jwt = JWT::encode($payload, $key, 'HS256');
            echo json_encode(['token' => $jwt]);
        } else {
            http_response_code(401);
            echo json_encode(['message' => 'Invalid credentials']);
        }
    } else {
        http_response_code(500);
        echo json_encode(['message' => 'Internal server error']);
    }
} else {
    http_response_code(405);
    echo json_encode(['message' => 'Method not allowed']);
}