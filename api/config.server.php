<?php
// Configurare conexiune bază de date MySQL - VERSIUNE SERVER (marc.ro)
// ⚠️ ÎNLOCUIEȘTE cu datele tale din cPanel!

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: https://marc.ro'); // Actualizat pentru marc.ro
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Dacă e request OPTIONS (preflight), oprește execuția
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Configurare bază de date - SERVER LIVE
// ⚠️ ÎNLOCUIEȘTE VALORILE DE MAI JOS CU CELE DIN cPanel!

define('DB_HOST', 'localhost');
define('DB_USER', 'username_admin');        // ← SCHIMBĂ cu user-ul tău MySQL din cPanel!
define('DB_PASS', 'parola_ta_sigura');      // ← SCHIMBĂ cu parola ta MySQL!
define('DB_NAME', 'username_anunturi');     // ← SCHIMBĂ cu numele bazei tale din cPanel!

// Exemplu real:
// define('DB_USER', 'marc_admin');
// define('DB_PASS', 'Xy#9mK$2pL@8');
// define('DB_NAME', 'marc_anunturi');

// Conectare la baza de date
function getDB() {
    try {
        $conn = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
        return $conn;
    } catch(PDOException $e) {
        http_response_code(500);
        
        // În producție, nu afișa detalii eroare!
        // Pentru debug temporal, decomentează linia de jos:
        // echo json_encode(['error' => 'Eroare conexiune bază de date: ' . $e->getMessage()]);
        
        echo json_encode(['error' => 'Eroare conexiune bază de date. Contactează administratorul.']);
        exit();
    }
}

// Funcție pentru verificare autentificare (JWT simplu sau sesiune)
function verificaAutentificare() {
    session_start();
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Neautentificat']);
        exit();
    }
    return $_SESSION['user_id'];
}

// Funcție pentru răspuns JSON
function raspuns($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit();
}

// Funcție pentru eroare
function eroare($mesaj, $status = 400) {
    http_response_code($status);
    echo json_encode(['error' => $mesaj], JSON_UNESCAPED_UNICODE);
    exit();
}

// Setări sesiune securizate (doar HTTPS pe server!)
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    ini_set('session.cookie_secure', '1');
}
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Strict');
?>

