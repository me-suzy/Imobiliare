<?php
/**
 * API pentru gestionarea parolelor (pentru admin)
 * Permite admin-ului să vadă parolele utilizatorilor
 * 
 * ⚠️ ATENȚIE: Acest API este doar pentru dezvoltare/admin
 * NU activa în producție pentru securitate maximă!
 */

// Dezactivează afișarea erorilor (pentru a nu polua JSON-ul)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Pornește output buffering pentru a preveni output accidental
ob_start();

// Setează header-urile JSON
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    ob_end_clean();
    exit();
}

require_once 'config.php';

// Curăță orice output anterior
ob_clean();

// Verifică dacă e admin
try {
    @session_start(); // Suprimă warning-uri pentru sesiune
    verificaAdmin();
} catch (Exception $e) {
    error_log('Eroare verificare admin: ' . $e->getMessage());
    ob_clean();
    http_response_code(403);
    echo json_encode(['error' => 'Nu ești autentificat ca admin'], JSON_UNESCAPED_UNICODE);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents('php://input'), true);

// Cheia de criptare - TREBUIE să fie aceeași în toate fișierele!
define('ENCRYPTION_KEY', 'marc_ro_secret_key_2024_change_this!');

/**
 * Criptează o parolă
 */
function encryptPassword($password) {
    $cipher = "AES-128-CBC";
    $ivlen = openssl_cipher_iv_length($cipher);
    $iv = openssl_random_pseudo_bytes($ivlen);
    $encrypted = openssl_encrypt($password, $cipher, ENCRYPTION_KEY, 0, $iv);
    return base64_encode($encrypted . '::' . $iv);
}

/**
 * Decriptează o parolă
 */
function decryptPassword($encryptedPassword) {
    $cipher = "AES-128-CBC";
    $data = base64_decode($encryptedPassword);
    list($encrypted_data, $iv) = explode('::', $data, 2);
    return openssl_decrypt($encrypted_data, $cipher, ENCRYPTION_KEY, 0, $iv);
}

// ========== OBȚINE TOATE PAROLELE (ADMIN) ==========
if ($method === 'GET') {
    
    try {
        $db = getDB();
        if (!$db) {
            ob_clean();
            http_response_code(500);
            echo json_encode(['error' => 'Conexiune la baza de date eșuată'], JSON_UNESCAPED_UNICODE);
            exit();
        }
        
        // Setează timeout scurt pentru a evita blocarea
        try {
            $db->setAttribute(PDO::ATTR_TIMEOUT, 3);
        } catch(PDOException $e) {
            error_log('Eroare la setarea timeout: ' . $e->getMessage());
        }
        
        // Verifică dacă tabelul există
        $tableExists = false;
        try {
            $stmt = $db->query("SHOW TABLES LIKE 'parole_admin'");
            $tableExists = $stmt->rowCount() > 0;
        } catch(PDOException $e) {
            error_log('Eroare la verificare tabel: ' . $e->getMessage());
            // Continuă fără tabelul parole_admin
        }
        
        // Obține utilizatori
        $users = [];
        try {
            $stmt = $db->query("SELECT id, nume, email, tip_cont FROM utilizatori ORDER BY id");
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log('Eroare la obținerea utilizatorilor: ' . $e->getMessage());
            ob_clean();
            http_response_code(500);
            echo json_encode(['error' => 'Eroare la interogare utilizatori: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
            exit();
        }
        
        if (empty($users)) {
            ob_clean();
            http_response_code(200);
            echo json_encode(['utilizatori' => []], JSON_UNESCAPED_UNICODE);
            exit();
        }
        
        // Obține parolele separate (pentru a evita JOIN-uri care pot bloca)
        $paroleMap = [];
        if ($tableExists) {
            try {
                $stmt = $db->query("SELECT id_utilizator, parola_criptata FROM parole_admin");
                $parole = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($parole as $parola) {
                    $paroleMap[$parola['id_utilizator']] = $parola['parola_criptata'];
                }
            } catch(PDOException $e) {
                // Dacă tabelul parole_admin nu există sau are probleme, continuă fără el
                error_log('Eroare la citire parole: ' . $e->getMessage());
            }
        }
        
        // Decriptează parolele
        $result = [];
        foreach ($users as $user) {
            $parola = 'Nu setată';
            $parolaCriptata = $paroleMap[$user['id']] ?? null;
            
            if (!empty($parolaCriptata)) {
                try {
                    $parola = decryptPassword($parolaCriptata);
                } catch(Exception $e) {
                    error_log('Eroare la decriptare parolă pentru user ' . $user['id'] . ': ' . $e->getMessage());
                    $parola = 'Eroare la decriptare';
                }
            } else {
                $parola = 'Nu sincronizat';
            }
            
            $result[] = [
                'id' => $user['id'],
                'nume' => $user['nume'],
                'email' => $user['email'],
                'tip_cont' => $user['tip_cont'] ?? 'user',
                'parola' => $parola
            ];
        }
        
        ob_clean();
        http_response_code(200);
        echo json_encode(['utilizatori' => $result], JSON_UNESCAPED_UNICODE);
        exit();
        
    } catch (Exception $e) {
        error_log('Eroare generală în GET: ' . $e->getMessage());
        ob_clean();
        http_response_code(500);
        echo json_encode(['error' => 'Eroare server: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
        exit();
    }
}

// ========== ACTUALIZEAZĂ PAROLA (ADMIN) ==========
else if ($method === 'POST') {
    
    try {
        $db = getDB();
        if (!$db) {
            ob_clean();
            http_response_code(500);
            echo json_encode(['error' => 'Conexiune la baza de date eșuată'], JSON_UNESCAPED_UNICODE);
            exit();
        }
    
    $userId = intval($data['id_utilizator'] ?? 0);
    $nouaParola = $data['parola'] ?? '';
    
    if (!$userId || !$nouaParola) {
        ob_clean();
        http_response_code(400);
        echo json_encode(['error' => 'ID utilizator și parolă sunt obligatorii'], JSON_UNESCAPED_UNICODE);
        exit();
    }
    
    // Verifică dacă utilizatorul există
    try {
        $stmt = $db->prepare("SELECT id FROM utilizatori WHERE id = ?");
        $stmt->execute([$userId]);
        if (!$stmt->fetch()) {
            ob_clean();
            http_response_code(404);
            echo json_encode(['error' => 'Utilizatorul nu există'], JSON_UNESCAPED_UNICODE);
            exit();
        }
    } catch(PDOException $e) {
        error_log('Eroare la verificare utilizator: ' . $e->getMessage());
        ob_clean();
        http_response_code(500);
        echo json_encode(['error' => 'Eroare la verificare utilizator: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
        exit();
    }
    
    // Criptează parola
    $encrypted = encryptPassword($nouaParola);
    
    // Hash-uiește parola pentru utilizatori (pentru login)
    $hashed = password_hash($nouaParola, PASSWORD_BCRYPT);
    
    // Actualizează în ambele tabele
    try {
        $db->beginTransaction();
        
        // Actualizează parola hash-uită în utilizatori
        $stmt = $db->prepare("UPDATE utilizatori SET parola = ? WHERE id = ?");
        $stmt->execute([$hashed, $userId]);
        
        // Verifică dacă tabelul parole_admin există și îl creează dacă nu există
        try {
            $stmt = $db->query("SHOW TABLES LIKE 'parole_admin'");
            if ($stmt->rowCount() == 0) {
                // Creează tabelul dacă nu există
                $db->exec("
                    CREATE TABLE IF NOT EXISTS parole_admin (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        id_utilizator INT NOT NULL UNIQUE,
                        parola_criptata TEXT NOT NULL,
                        data_creare DATETIME DEFAULT CURRENT_TIMESTAMP,
                        data_actualizare DATETIME ON UPDATE CURRENT_TIMESTAMP,
                        INDEX idx_utilizator (id_utilizator)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ");
            }
        } catch(PDOException $e) {
            error_log('Eroare la creare/verificare tabel parole_admin: ' . $e->getMessage());
        }
        
        // Actualizează parola criptată în parole_admin
        try {
            $stmt = $db->prepare("
                INSERT INTO parole_admin (id_utilizator, parola_criptata) 
                VALUES (?, ?) 
                ON DUPLICATE KEY UPDATE parola_criptata = ?, data_actualizare = NOW()
            ");
            $stmt->execute([$userId, $encrypted, $encrypted]);
        } catch(PDOException $e) {
            error_log('Eroare la actualizare parole_admin: ' . $e->getMessage());
            // Continuă chiar dacă parole_admin eșuează
        }
        
        $db->commit();
        
        ob_clean();
        http_response_code(200);
        echo json_encode(['success' => true, 'mesaj' => 'Parola actualizată cu succes'], JSON_UNESCAPED_UNICODE);
        exit();
    } catch(PDOException $e) {
        if ($db && $db->inTransaction()) {
            $db->rollBack();
        }
        error_log('Eroare la actualizarea parolei: ' . $e->getMessage());
        ob_clean();
        http_response_code(500);
        echo json_encode(['error' => 'Eroare la actualizarea parolei: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
        exit();
    }
    
    } catch (Exception $e) {
        error_log('Eroare generală în POST: ' . $e->getMessage());
        ob_clean();
        http_response_code(500);
        echo json_encode(['error' => 'Eroare server: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
        exit();
    }
}

else {
    ob_clean();
    http_response_code(405);
    echo json_encode(['error' => 'Acțiune invalidă'], JSON_UNESCAPED_UNICODE);
    exit();
}

?>

