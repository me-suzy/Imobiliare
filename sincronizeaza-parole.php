<?php
/**
 * Script pentru sincronizarea parolelor existente în tabelul parole_admin
 * Accesează: http://localhost/sincronizeaza-parole.php
 */

header('Content-Type: text/html; charset=utf-8');

// Configurare conexiune
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'anunturi_db';

// Cheia de criptare (trebuie să fie aceeași ca în admin-parole.php)
define('ENCRYPTION_KEY', 'marc_ro_secret_key_2024_change_this!');

function encryptPassword($password) {
    $cipher = "AES-128-CBC";
    $ivlen = openssl_cipher_iv_length($cipher);
    $iv = openssl_random_pseudo_bytes($ivlen);
    $encrypted = openssl_encrypt($password, $cipher, ENCRYPTION_KEY, 0, $iv);
    return base64_encode($encrypted . '::' . $iv);
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>🔄 Sincronizare Parole Admin</h2>";
    echo "<style>
        body { font-family: Arial, sans-serif; padding: 20px; max-width: 800px; margin: 0 auto; }
        .success { color: green; font-weight: bold; padding: 10px; background: #d4edda; border-radius: 5px; margin: 10px 0; }
        .error { color: red; font-weight: bold; padding: 10px; background: #f8d7da; border-radius: 5px; margin: 10px 0; }
        .info { color: blue; padding: 10px; background: #d1ecf1; border-radius: 5px; margin: 10px 0; }
        table { border-collapse: collapse; width: 100%; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #4CAF50; color: white; }
    </style>";
    
    // Pasul 1: Creează tabelul dacă nu există
    echo "<h3>📋 Pasul 1: Verificare/Creare tabel parole_admin</h3>";
    
    $stmt = $pdo->query("SHOW TABLES LIKE 'parole_admin'");
    $tableExists = $stmt->rowCount() > 0;
    
    if (!$tableExists) {
        echo "<div class='info'>Tabelul nu există. Se creează...</div>";
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS parole_admin (
                id INT AUTO_INCREMENT PRIMARY KEY,
                id_utilizator INT NOT NULL UNIQUE,
                parola_criptata TEXT NOT NULL,
                data_creare DATETIME DEFAULT CURRENT_TIMESTAMP,
                data_actualizare DATETIME ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (id_utilizator) REFERENCES utilizatori(id) ON DELETE CASCADE,
                INDEX idx_utilizator (id_utilizator)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        echo "<div class='success'>✅ Tabelul parole_admin a fost creat!</div>";
    } else {
        echo "<div class='info'>✅ Tabelul parole_admin există deja.</div>";
    }
    
    // Pasul 2: Obține toți utilizatorii
    echo "<h3>👥 Pasul 2: Obținere utilizatori</h3>";
    
    $stmt = $pdo->query("SELECT id, nume, email FROM utilizatori ORDER BY id");
    $utilizatori = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($utilizatori)) {
        echo "<div class='error'>❌ Nu există utilizatori în baza de date!</div>";
        echo "<p>Te rugăm să creezi utilizatori mai întâi prin:</p>";
        echo "<ul>";
        echo "<li>Înregistrare prin site: <a href='login.html'>login.html</a></li>";
        echo "<li>Sau adaugă manual în phpMyAdmin</li>";
        echo "</ul>";
        exit;
    }
    
    echo "<div class='success'>✅ Găsiți " . count($utilizatori) . " utilizatori.</div>";
    
    // Pasul 3: Sincronizează parolele
    echo "<h3>🔐 Pasul 3: Sincronizare parole</h3>";
    
    $parolaDefault = 'password'; // Parola standard
    $encryptedDefault = encryptPassword($parolaDefault);
    
    $sincronizati = 0;
    $actualizati = 0;
    
    // Verifică dacă există deja parole
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM parole_admin");
    $totalExistent = $stmt->fetch()['total'];
    
    if ($totalExistent > 0 && isset($_GET['force']) && $_GET['force'] === 'yes') {
        echo "<div class='info'>Forțând actualizarea tuturor parolelor...</div>";
    } elseif ($totalExistent > 0 && !isset($_GET['force'])) {
        echo "<div class='info'>";
        echo "⚠️ Există deja {$totalExistent} parole în tabel. ";
        echo "<a href='?force=yes' style='color: blue;'>Forțează actualizarea tuturor →</a>";
        echo "</div>";
    }
    
    echo "<table>";
    echo "<tr><th>ID</th><th>Email</th><th>Nume</th><th>Status</th><th>Parolă Setată</th></tr>";
    
    $forceUpdate = isset($_GET['force']) && $_GET['force'] === 'yes';
    
    foreach ($utilizatori as $user) {
        // Verifică dacă utilizatorul are deja parolă criptată
        $stmt = $pdo->prepare("SELECT id FROM parole_admin WHERE id_utilizator = ?");
        $stmt->execute([$user['id']]);
        $exists = $stmt->fetch();
        
        if ($exists && !$forceUpdate) {
            $actualizati++;
            $status = "⏭️ Deja există";
            $parolaSetata = "password";
        } else {
            if ($exists) {
                // Actualizează parola existentă
                $stmt = $pdo->prepare("UPDATE parole_admin SET parola_criptata = ? WHERE id_utilizator = ?");
                $stmt->execute([$encryptedDefault, $user['id']]);
                $actualizati++;
                $status = "✅ Actualizat";
            } else {
                // Inserează parola nouă
                $stmt = $pdo->prepare("INSERT INTO parole_admin (id_utilizator, parola_criptata) VALUES (?, ?) ON DUPLICATE KEY UPDATE parola_criptata = ?");
                $stmt->execute([$user['id'], $encryptedDefault, $encryptedDefault]);
                $sincronizati++;
                $status = "🆕 Adăugat";
            }
            $parolaSetata = $parolaDefault;
        }
        
        echo "<tr>";
        echo "<td>{$user['id']}</td>";
        echo "<td>" . htmlspecialchars($user['email']) . "</td>";
        echo "<td>" . htmlspecialchars($user['nume']) . "</td>";
        echo "<td>{$status}</td>";
        echo "<td><strong>{$parolaSetata}</strong></td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    // Rezumat
    echo "<div class='success'>";
    echo "<h3>✅ Sincronizare completă!</h3>";
    echo "<p><strong>Adăugați:</strong> {$sincronizati} utilizatori</p>";
    echo "<p><strong>Actualizați:</strong> {$actualizati} utilizatori</p>";
    echo "<p><strong>Parola standard setată:</strong> <code>{$parolaDefault}</code></p>";
    echo "</div>";
    
    // Verificare finală
    echo "<h3>🔍 Verificare Finală</h3>";
    $stmt = $pdo->query("
        SELECT COUNT(*) as total 
        FROM parole_admin
    ");
    $total = $stmt->fetch()['total'];
    
    echo "<div class='info'>";
    echo "✅ Total parole în tabelul parole_admin: <strong>{$total}</strong>";
    echo "</div>";
    
    // Link către admin-parole.html
    echo "<div style='margin-top: 30px; padding: 20px; background: #e3f2fd; border-radius: 5px;'>";
    echo "<h3>🎯 Următorul Pas</h3>";
    echo "<p>Acum poți accesa panoul de parole:</p>";
    echo "<p><a href='admin-parole.html' style='background: #4CAF50; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold;'>Vizualizează Parole →</a></p>";
    echo "</div>";
    
} catch(PDOException $e) {
    echo "<div class='error'>";
    echo "<h3>❌ Eroare:</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

?>

