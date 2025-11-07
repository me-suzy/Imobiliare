<?php
/**
 * Script pentru resetarea/rescrierea parolelor utilizatorilor
 * Accesează: http://localhost/reset-parole.php
 * 
 * ⚠️ ATENȚIE: Șterge acest fișier după utilizare pentru securitate!
 */

header('Content-Type: text/html; charset=utf-8');

// Configurare conexiune
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'anunturi_db';

// Lista parolelor standard (hash-uite cu bcrypt)
$paroleStandard = [
    'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'admin123' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
];

try {
    // Conectare la MySQL
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>🔐 Resetare Parole Utilizatori</h2>";
    echo "<style>
        body { font-family: Arial, sans-serif; padding: 20px; max-width: 800px; margin: 0 auto; }
        table { border-collapse: collapse; width: 100%; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #4CAF50; color: white; }
        tr:nth-child(even) { background-color: #f2f2f2; }
        .success { color: green; font-weight: bold; }
        .info { background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .warning { background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0; }
    </style>";
    
    // Lista utilizatorilor și parolele lor
    echo "<div class='info'>";
    echo "<h3>📋 Parole Standard:</h3>";
    echo "<ul>";
    echo "<li><strong>test@example.com</strong> → Parola: <code>password</code></li>";
    echo "<li><strong>admin@marc.ro</strong> → Parola: <code>password</code></li>";
    echo "<li><strong>ionel@example.com</strong> → Parola: <code>password</code></li>";
    echo "</ul>";
    echo "</div>";
    
    // Obține toți utilizatorii
    $stmt = $pdo->query("SELECT id, nume, email, tip_cont FROM utilizatori ORDER BY id");
    $utilizatori = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>👥 Utilizatori în baza de date:</h3>";
    echo "<table>";
    echo "<tr><th>ID</th><th>Nume</th><th>Email</th><th>Tip Cont</th><th>Parola Hash</th><th>Acțiuni</th></tr>";
    
    foreach ($utilizatori as $user) {
        // Obține hash-ul parolei
        $stmt = $pdo->prepare("SELECT parola FROM utilizatori WHERE id = ?");
        $stmt->execute([$user['id']]);
        $parolaHash = $stmt->fetchColumn();
        
        echo "<tr>";
        echo "<td>{$user['id']}</td>";
        echo "<td>" . htmlspecialchars($user['nume']) . "</td>";
        echo "<td>" . htmlspecialchars($user['email']) . "</td>";
        echo "<td><strong>" . htmlspecialchars($user['tip_cont']) . "</strong></td>";
        echo "<td style='font-size: 10px; word-break: break-all;'>" . substr($parolaHash, 0, 50) . "...</td>";
        echo "<td>";
        echo "<a href='?reset={$user['id']}' style='color: blue;'>Reset la 'password'</a>";
        echo "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    // Procesează resetarea parolei
    if (isset($_GET['reset'])) {
        $userId = intval($_GET['reset']);
        
        // Hash pentru parola "password"
        $newPasswordHash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
        
        $stmt = $pdo->prepare("UPDATE utilizatori SET parola = ? WHERE id = ?");
        $stmt->execute([$newPasswordHash, $userId]);
        
        // Obține email-ul utilizatorului
        $stmt = $pdo->prepare("SELECT email, nume FROM utilizatori WHERE id = ?");
        $stmt->execute([$userId]);
        $userInfo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "<div class='success'>";
        echo "✅ Parola pentru <strong>{$userInfo['email']}</strong> a fost resetată la: <code>password</code>";
        echo "</div>";
        
        echo "<script>setTimeout(function(){ window.location.href='reset-parole.php'; }, 2000);</script>";
    }
    
    // Formular pentru generare hash nou
    echo "<div class='info'>";
    echo "<h3>🔧 Generează Hash Nou pentru Parolă:</h3>";
    echo "<form method='POST' style='margin-top: 10px;'>";
    echo "<label>Email utilizator: </label>";
    echo "<select name='user_email' required>";
    foreach ($utilizatori as $user) {
        echo "<option value='{$user['email']}'>{$user['email']} ({$user['nume']})</option>";
    }
    echo "</select><br><br>";
    echo "<label>Parolă nouă: </label>";
    echo "<input type='password' name='new_password' required minlength='6'><br><br>";
    echo "<button type='submit' style='background: #4CAF50; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>Actualizează Parola</button>";
    echo "</form>";
    echo "</div>";
    
    // Procesează schimbarea parolei
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_email']) && isset($_POST['new_password'])) {
        $userEmail = $_POST['user_email'];
        $newPassword = $_POST['new_password'];
        
        // Generează hash nou
        $newPasswordHash = password_hash($newPassword, PASSWORD_BCRYPT);
        
        $stmt = $pdo->prepare("UPDATE utilizatori SET parola = ? WHERE email = ?");
        $stmt->execute([$newPasswordHash, $userEmail]);
        
        echo "<div class='success'>";
        echo "✅ Parola pentru <strong>{$userEmail}</strong> a fost actualizată!";
        echo "<br>Hash nou: <code style='font-size: 10px;'>" . substr($newPasswordHash, 0, 50) . "...</code>";
        echo "<br><strong>Parola setată: {$newPassword}</strong>";
        echo "</div>";
    }
    
    echo "<div class='warning'>";
    echo "<strong>⚠️ ATENȚIE:</strong>";
    echo "<ul>";
    echo "<li>Parolele din baza de date sunt hash-uite cu bcrypt pentru securitate</li>";
    echo "<li>Nu poți 'decripta' un hash - poți doar să-l verifici</li>";
    echo "<li>Parola standard pentru toți utilizatorii de test este: <strong>password</strong></li>";
    echo "<li>Șterge acest fișier după utilizare pentru securitate!</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<p><a href='index.html'>← Înapoi la site</a></p>";
    
} catch(PDOException $e) {
    echo "<h3 style='color: red;'>❌ Eroare:</h3>";
    echo "<p style='color: red;'>" . htmlspecialchars($e->getMessage()) . "</p>";
}

?>

