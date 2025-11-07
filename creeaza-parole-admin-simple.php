<?php
/**
 * Creează tabelul parole_admin SIMPLIFICAT (fără foreign keys care blochează)
 * Accesează: http://localhost/creeaza-parole-admin-simple.php
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('max_execution_time', 30);

header('Content-Type: text/html; charset=utf-8');

echo "<h1>🔧 Creează Tabel parole_admin (Simplificat)</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; padding: 20px; max-width: 800px; margin: 0 auto; }
    .success { color: green; padding: 10px; background: #d4edda; border-radius: 5px; margin: 10px 0; }
    .error { color: red; padding: 10px; background: #f8d7da; border-radius: 5px; margin: 10px 0; }
    .info { color: blue; padding: 10px; background: #d1ecf1; border-radius: 5px; margin: 10px 0; }
</style>";

try {
    $pdo = new PDO("mysql:host=localhost;dbname=anunturi_db", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<div class='info'>✅ Conexiune la baza de date reușită!</div>";
    
    // Verifică dacă tabelul există
    $stmt = $pdo->query("SHOW TABLES LIKE 'parole_admin'");
    $exists = $stmt->rowCount() > 0;
    
    if ($exists) {
        echo "<div class='info'>⚠️ Tabelul parole_admin există deja.</div>";
        echo "<p>Dorești să-l ștergi și să-l recreezi? <a href='?recreate=yes' style='color: red;'>DA, șterge și recreează</a></p>";
        
        if (isset($_GET['recreate']) && $_GET['recreate'] === 'yes') {
            echo "<div class='info'>Se șterge tabelul...</div>";
            $pdo->exec("DROP TABLE IF EXISTS parole_admin");
            $exists = false;
        }
    }
    
    if (!$exists) {
        echo "<div class='info'>Se creează tabelul (SIMPLIFICAT - fără foreign key)...</div>";
        
        // Creează tabelul FĂRĂ foreign key pentru a evita blocarea phpMyAdmin
        $pdo->exec("
            CREATE TABLE parole_admin (
                id INT AUTO_INCREMENT PRIMARY KEY,
                id_utilizator INT NOT NULL UNIQUE,
                parola_criptata TEXT NOT NULL,
                data_creare DATETIME DEFAULT CURRENT_TIMESTAMP,
                data_actualizare DATETIME ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_utilizator (id_utilizator)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        echo "<div class='success'>✅ Tabelul parole_admin a fost creat cu succes!</div>";
    }
    
    // Verifică utilizatori
    echo "<h2>📋 Utilizatori</h2>";
    $stmt = $pdo->query("SELECT id, nume, email, tip_cont FROM utilizatori ORDER BY id");
    $users = $stmt->fetchAll();
    
    if (empty($users)) {
        echo "<div class='error'>❌ Nu există utilizatori în baza de date!</div>";
        echo "<p>Creează utilizatori prin <a href='login.html'>login.html</a> (înregistrare)</p>";
    } else {
        echo "<div class='success'>✅ Găsiți " . count($users) . " utilizatori.</div>";
        
        // Sincronizează parolele
        echo "<h2>🔐 Sincronizare Parole</h2>";
        
        $encryptionKey = 'marc_ro_secret_key_2024_change_this!';
        $cipher = "AES-128-CBC";
        $parolaDefault = 'password';
        
        $sincronizati = 0;
        $actualizati = 0;
        
        foreach ($users as $user) {
            // Criptează parola
            $ivlen = openssl_cipher_iv_length($cipher);
            $iv = openssl_random_pseudo_bytes($ivlen);
            $encrypted = openssl_encrypt($parolaDefault, $cipher, $encryptionKey, 0, $iv);
            $encryptedPassword = base64_encode($encrypted . '::' . $iv);
            
            // Verifică dacă există deja
            $stmt = $pdo->prepare("SELECT id FROM parole_admin WHERE id_utilizator = ?");
            $stmt->execute([$user['id']]);
            $exists = $stmt->fetch();
            
            if ($exists) {
                // Actualizează
                $stmt = $pdo->prepare("UPDATE parole_admin SET parola_criptata = ? WHERE id_utilizator = ?");
                $stmt->execute([$encryptedPassword, $user['id']]);
                $actualizati++;
            } else {
                // Inserează
                $stmt = $pdo->prepare("INSERT INTO parole_admin (id_utilizator, parola_criptata) VALUES (?, ?)");
                $stmt->execute([$user['id'], $encryptedPassword]);
                $sincronizati++;
            }
        }
        
        echo "<div class='success'>";
        echo "<h3>✅ Sincronizare completă!</h3>";
        echo "<p><strong>Adăugați:</strong> {$sincronizati} utilizatori</p>";
        echo "<p><strong>Actualizați:</strong> {$actualizati} utilizatori</p>";
        echo "<p><strong>Parola setată pentru toți:</strong> <code>{$parolaDefault}</code></p>";
        echo "</div>";
        
        // Afișează utilizatorii
        echo "<h2>👥 Utilizatori și Parole</h2>";
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Nume</th><th>Email</th><th>Tip Cont</th><th>Parolă</th></tr>";
        
        foreach ($users as $user) {
            // Decriptează parola
            $stmt = $pdo->prepare("SELECT parola_criptata FROM parole_admin WHERE id_utilizator = ?");
            $stmt->execute([$user['id']]);
            $result = $stmt->fetch();
            
            $parola = 'Nu setată';
            if ($result && !empty($result['parola_criptata'])) {
                try {
                    $data = base64_decode($result['parola_criptata']);
                    list($encrypted_data, $iv_decrypt) = explode('::', $data, 2);
                    $parola = openssl_decrypt($encrypted_data, $cipher, $encryptionKey, 0, $iv_decrypt);
                } catch(Exception $e) {
                    $parola = 'Eroare';
                }
            }
            
            echo "<tr>";
            echo "<td>{$user['id']}</td>";
            echo "<td>" . htmlspecialchars($user['nume']) . "</td>";
            echo "<td>" . htmlspecialchars($user['email']) . "</td>";
            echo "<td><strong>{$user['tip_cont']}</strong></td>";
            echo "<td><code>{$parola}</code></td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<hr>";
    echo "<h2>🎯 Următorul Pas</h2>";
    echo "<div class='info'>";
    echo "<p>1. Loghează-te ca admin: <a href='login.html'>login.html</a></p>";
    echo "<p>2. Email: <strong>admin@marc.ro</strong> / Parolă: <strong>password</strong></p>";
    echo "<p>3. Accesează: <a href='admin-parole.html'>admin-parole.html</a></p>";
    echo "</div>";
    
} catch(PDOException $e) {
    echo "<div class='error'>";
    echo "<h3>❌ Eroare:</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

?>

