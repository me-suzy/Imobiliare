<?php
/**
 * Script pentru instalarea tabelului sesiuni_logare
 * Accesează: http://localhost/install-sesiuni-logare.php
 */

header('Content-Type: text/html; charset=utf-8');

// Configurare conexiune
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'anunturi_db';

try {
    // Conectare la MySQL
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>📊 Instalare Tabel Sesiuni Logare</h2>";
    echo "<p>Se conectează la baza de date...</p>";
    
    // Selectează baza de date
    $pdo->exec("USE $dbname");
    echo "<p>✅ Baza de date '$dbname' selectată</p>";
    
    // Verifică dacă tabelul există deja
    $stmt = $pdo->query("SHOW TABLES LIKE 'sesiuni_logare'");
    $exists = $stmt->rowCount() > 0;
    
    if ($exists) {
        echo "<p>⚠️ Tabelul 'sesiuni_logare' există deja. Se va actualiza structura...</p>";
        // Nu șterge tabelul, doar verifică dacă are toate coloanele necesare
    } else {
        echo "<p>📝 Se creează tabelul 'sesiuni_logare'...</p>";
    }
    
    // Creează tabelul (dacă nu există)
    $sql = "CREATE TABLE IF NOT EXISTS sesiuni_logare (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_utilizator INT NOT NULL,
        ip_address VARCHAR(45),
        user_agent TEXT,
        metoda_autentificare ENUM('email', 'facebook', 'google', 'apple') DEFAULT 'email',
        data_logare DATETIME DEFAULT CURRENT_TIMESTAMP,
        data_logout DATETIME,
        activ BOOLEAN DEFAULT TRUE,
        FOREIGN KEY (id_utilizator) REFERENCES utilizatori(id) ON DELETE CASCADE,
        INDEX idx_utilizator (id_utilizator),
        INDEX idx_data_logare (data_logare DESC),
        INDEX idx_activ (activ)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
    echo "<p>✅ Tabelul 'sesiuni_logare' creat cu succes!</p>";
    
    // Verifică structura
    $stmt = $pdo->query("DESCRIBE sesiuni_logare");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>📋 Structura tabelului:</h3>";
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($col['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Verifică dacă există date
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM sesiuni_logare");
    $total = $stmt->fetch()['total'];
    
    echo "<p><strong>📊 Total sesiuni înregistrate: $total</strong></p>";
    
    if ($total > 0) {
        echo "<h3>🔍 Ultimele 5 sesiuni:</h3>";
        $stmt = $pdo->query("
            SELECT s.*, u.nume, u.email 
            FROM sesiuni_logare s
            JOIN utilizatori u ON s.id_utilizator = u.id
            ORDER BY s.data_logare DESC
            LIMIT 5
        ");
        $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Utilizator</th><th>Email</th><th>Metodă</th><th>IP</th><th>Data Logare</th><th>Status</th></tr>";
        foreach ($sessions as $s) {
            echo "<tr>";
            echo "<td>" . $s['id'] . "</td>";
            echo "<td>" . htmlspecialchars($s['nume']) . "</td>";
            echo "<td>" . htmlspecialchars($s['email']) . "</td>";
            echo "<td>" . htmlspecialchars($s['metoda_autentificare']) . "</td>";
            echo "<td>" . htmlspecialchars($s['ip_address'] ?? '-') . "</td>";
            echo "<td>" . $s['data_logare'] . "</td>";
            echo "<td>" . ($s['activ'] ? '✅ Activ' : '❌ Închis') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<br><p style='color: green; font-weight: bold;'>✅ Instalare completă cu succes!</p>";
    echo "<p><a href='admin.html'>← Înapoi la Admin Panel</a></p>";
    
} catch(PDOException $e) {
    echo "<h3 style='color: red;'>❌ Eroare:</h3>";
    echo "<p style='color: red;'>" . htmlspecialchars($e->getMessage()) . "</p>";
    
    if (strpos($e->getMessage(), "Unknown database") !== false) {
        echo "<p><strong>💡 Soluție:</strong> Baza de date '$dbname' nu există. Te rugăm să o creezi mai întâi:</p>";
        echo "<pre>CREATE DATABASE $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;</pre>";
    }
    
    if (strpos($e->getMessage(), "Access denied") !== false) {
        echo "<p><strong>💡 Soluție:</strong> Verifică userul și parola în config.php</p>";
    }
}

?>

