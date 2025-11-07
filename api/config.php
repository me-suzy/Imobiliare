<?php
// Configurare conexiune bază de date MySQL - VERSIUNE LOCAL (XAMPP)
// Pentru server LIVE (marc.ro), folosește config.server.php!

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Dacă e request OPTIONS (preflight), oprește execuția
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Configurare bază de date - LOCAL (XAMPP)
define('DB_HOST', 'localhost');
define('DB_USER', 'root');                // User implicit XAMPP
define('DB_PASS', '');                    // Parola goală în XAMPP
define('DB_NAME', 'anunturi_db');         // Numele bazei tale locale

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
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_TIMEOUT => 3, // Timeout scurt pentru a evita blocarea
                PDO::ATTR_PERSISTENT => false // Nu folosi conexiuni persistente
            ]
        );
        return $conn;
    } catch(PDOException $e) {
        // Log eroarea dar nu opri execuția pentru verificarea sesiunii
        error_log('Eroare conexiune DB: ' . $e->getMessage());
        // Returnează null în loc să oprească execuția
        return null;
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

// Funcție pentru verificare admin
function verificaAdmin() {
    session_start();
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Neautentificat']);
        exit();
    }
    
    if (!isset($_SESSION['tip_cont']) || $_SESSION['tip_cont'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Acces interzis. Doar administratorii au acces.']);
        exit();
    }
    
    return $_SESSION['user_id'];
}

// Funcție pentru obținere tip cont
function getTipCont() {
    session_start();
    return $_SESSION['tip_cont'] ?? 'user';
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
?>

