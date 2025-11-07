<?php
require_once 'config.php';

// Obține conexiunea DB (poate fi null dacă eșuează)
$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents('php://input'), true);

// Setează error reporting pentru a evita erorile de warning
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);

// ========== ÎNREGISTRARE ==========
if ($method === 'POST' && isset($data['action']) && $data['action'] === 'register') {
    
    $nume = trim($data['nume'] ?? '');
    $email = trim($data['email'] ?? '');
    $parola = $data['parola'] ?? '';
    $telefon = trim($data['telefon'] ?? '');
    $judet = trim($data['judet'] ?? '');
    $adresa = trim($data['adresa'] ?? '');
    
    // Validare
    if (empty($nume) || empty($email) || empty($parola)) {
        eroare('Toate câmpurile sunt obligatorii');
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        eroare('Email invalid');
    }
    
    if (strlen($parola) < 6) {
        eroare('Parola trebuie să aibă minim 6 caractere');
    }
    
    // Verifică dacă email-ul există deja
    $stmt = $db->prepare("SELECT id FROM utilizatori WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        eroare('Email-ul este deja înregistrat');
    }
    
    // Hash parolă
    $parolaHash = password_hash($parola, PASSWORD_DEFAULT);
    
    // Inserare utilizator
    try {
        $db->beginTransaction();
        
        // Inserează utilizatorul
        $stmt = $db->prepare("
            INSERT INTO utilizatori (nume, email, parola, telefon, data_creare) 
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$nume, $email, $parolaHash, $telefon]);
        
        $userId = $db->lastInsertId();
        
        // Salvează parola criptată pentru admin (dacă tabelul există)
        try {
            // Verifică dacă tabelul există
            $stmt = $db->query("SHOW TABLES LIKE 'parole_admin'");
            if ($stmt->rowCount() > 0) {
                // Cheia de criptare (trebuie să fie aceeași ca în admin-parole.php)
                $encryptionKey = 'marc_ro_secret_key_2024_change_this!';
                $cipher = "AES-128-CBC";
                $ivlen = openssl_cipher_iv_length($cipher);
                $iv = openssl_random_pseudo_bytes($ivlen);
                $encrypted = openssl_encrypt($parola, $cipher, $encryptionKey, 0, $iv);
                $encryptedPassword = base64_encode($encrypted . '::' . $iv);
                
                $stmt = $db->prepare("
                    INSERT INTO parole_admin (id_utilizator, parola_criptata) 
                    VALUES (?, ?)
                ");
                $stmt->execute([$userId, $encryptedPassword]);
            }
        } catch(PDOException $e) {
            // Ignoră eroarea dacă tabelul nu există
            error_log('Eroare la salvare parola criptată: ' . $e->getMessage());
        }
        
        $db->commit();
        
        // Pornește sesiunea
        session_start();
        $_SESSION['user_id'] = $userId;
        $_SESSION['email'] = $email;
        $_SESSION['nume'] = $nume;
        $_SESSION['telefon'] = $telefon;
        $_SESSION['judet'] = null;
        $_SESSION['adresa'] = null;
        $_SESSION['tip_cont'] = 'user';
        
        raspuns([
            'success' => true,
            'mesaj' => 'Cont creat cu succes!',
            'user' => [
                'id' => $userId,
                'nume' => $nume,
                'email' => $email,
                'telefon' => $telefon,
                'judet' => null,
                'adresa' => null,
                'tip_cont' => 'user',
                'sold_cont' => 0.00,
                'credite_disponibile' => 0
            ]
        ]);
        
    } catch(PDOException $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        eroare('Eroare la creare cont: ' . $e->getMessage(), 500);
    }
}

// ========== LOGIN ==========
else if ($method === 'POST' && isset($data['action']) && $data['action'] === 'login') {
    
    $email = trim($data['email'] ?? '');
    $parola = $data['parola'] ?? '';
    
    if (empty($email) || empty($parola)) {
        eroare('Email și parola sunt obligatorii');
    }
    
    // Caută utilizatorul
    $stmt = $db->prepare("SELECT * FROM utilizatori WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if (!$user || !password_verify($parola, $user['parola'])) {
        eroare('Email sau parolă incorectă');
    }
    
        // Pornește sesiunea
        session_start();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['nume'] = $user['nume'];
        $_SESSION['telefon'] = $user['telefon'];
        $_SESSION['judet'] = $user['judet'] ?? null;
        $_SESSION['adresa'] = $user['adresa'] ?? null;
        $_SESSION['tip_cont'] = $user['tip_cont'] ?? 'user';
        
        // Înregistrează sesiunea de logare
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        
        try {
            $stmt = $db->prepare("
                INSERT INTO sesiuni_logare (id_utilizator, ip_address, user_agent, metoda_autentificare) 
                VALUES (?, ?, ?, 'email')
            ");
            $stmt->execute([$user['id'], $ipAddress, $userAgent]);
        } catch(PDOException $e) {
            // Ignoră eroarea dacă tabelul nu există încă
            error_log('Eroare la înregistrarea sesiunii: ' . $e->getMessage());
        }
        
        raspuns([
            'success' => true,
            'mesaj' => 'Autentificare reușită!',
            'user' => [
                'id' => $user['id'],
                'nume' => $user['nume'],
                'email' => $user['email'],
                'telefon' => $user['telefon'],
                'judet' => $user['judet'],
                'adresa' => $user['adresa'],
                'tip_cont' => $user['tip_cont'] ?? 'user',
                'sold_cont' => floatval($user['sold_cont'] ?? 0),
                'credite_disponibile' => intval($user['credite_disponibile'] ?? 0)
            ]
        ]);
}

// ========== VERIFICĂ SESIUNE ==========
else if ($method === 'GET' && isset($_GET['action']) && $_GET['action'] === 'check') {
    // Încearcă să pornească sesiunea (nu opri execuția dacă eșuează)
    @session_start();
    
    // Dacă nu există sesiune sau user_id, returnează neautentificat
    if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
        raspuns(['autentificat' => false]);
    }
    
    // Verifică conexiunea la DB
    if (!$db) {
        // Dacă DB nu e disponibilă, folosește datele din sesiune
        if (isset($_SESSION['user_id'])) {
            raspuns([
                'autentificat' => true,
                'user' => [
                    'id' => $_SESSION['user_id'],
                    'nume' => $_SESSION['nume'] ?? 'Utilizator',
                    'email' => $_SESSION['email'] ?? '',
                    'telefon' => $_SESSION['telefon'] ?? '',
                    'tip_cont' => $_SESSION['tip_cont'] ?? 'user',
                    'sold_cont' => 0,
                    'credite_disponibile' => 0
                ]
            ]);
        } else {
            raspuns(['autentificat' => false]);
        }
    }
    
    // Preia datele utilizatorului din DB
    try {
        $stmt = $db->prepare("SELECT id, nume, email, telefon, judet, adresa, tip_cont, sold_cont, credite_disponibile FROM utilizatori WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        
        if ($user) {
            // IMPORTANT: Folosește tip_cont din DB (sursa de adevăr) și actualizează sesiunea
            $tipCont = $user['tip_cont'] ?? 'user';
            
            // Actualizează sesiunea cu tip_cont din DB (pentru sincronizare)
            $_SESSION['tip_cont'] = $tipCont;
            $_SESSION['nume'] = $user['nume'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['telefon'] = $user['telefon'];
            $_SESSION['judet'] = $user['judet'];
            $_SESSION['adresa'] = $user['adresa'];
            
            raspuns([
                'autentificat' => true,
                'user' => [
                    'id' => $user['id'],
                    'nume' => $user['nume'],
                    'email' => $user['email'],
                    'telefon' => $user['telefon'],
                    'judet' => $user['judet'],
                    'adresa' => $user['adresa'],
                    'tip_cont' => $tipCont,
                    'sold_cont' => floatval($user['sold_cont'] ?? 0),
                    'credite_disponibile' => intval($user['credite_disponibile'] ?? 0)
                ]
            ]);
        } else {
            // User-ul nu există în DB, șterge sesiunea
            session_destroy();
            raspuns(['autentificat' => false]);
        }
    } catch(PDOException $e) {
        // Dacă e eroare DB, folosește datele din sesiune
        error_log('Eroare DB la check: ' . $e->getMessage());
        if (isset($_SESSION['user_id'])) {
            raspuns([
                'autentificat' => true,
                'user' => [
                    'id' => $_SESSION['user_id'],
                    'nume' => $_SESSION['nume'] ?? 'Utilizator',
                    'email' => $_SESSION['email'] ?? '',
                    'telefon' => $_SESSION['telefon'] ?? '',
                    'judet' => $_SESSION['judet'] ?? '',
                    'adresa' => $_SESSION['adresa'] ?? '',
                    'tip_cont' => $_SESSION['tip_cont'] ?? 'user',
                    'sold_cont' => 0,
                    'credite_disponibile' => 0
                ]
            ]);
        } else {
            raspuns(['autentificat' => false]);
        }
    }
}

// ========== LOGOUT ==========
else if ($method === 'POST' && isset($data['action']) && $data['action'] === 'logout') {
    session_start();
    $userId = $_SESSION['user_id'] ?? null;
    
    // Marchează sesiunea ca închisă
    if ($userId) {
        try {
            $stmt = $db->prepare("
                UPDATE sesiuni_logare 
                SET activ = FALSE, data_logout = NOW() 
                WHERE id_utilizator = ? AND activ = TRUE 
                ORDER BY data_logare DESC 
                LIMIT 1
            ");
            $stmt->execute([$userId]);
        } catch(PDOException $e) {
            // Ignoră eroarea dacă tabelul nu există
            error_log('Eroare la închiderea sesiunii: ' . $e->getMessage());
        }
    }
    
    session_destroy();
    raspuns(['success' => true, 'mesaj' => 'Deconectat cu succes']);
}

// ========== UPDATE PROFILE ==========
else if ($method === 'POST' && isset($data['action']) && $data['action'] === 'update_profile') {
    session_start();
    
    if (!isset($_SESSION['user_id'])) {
        eroare('Nu ești autentificat');
    }
    
    $userId = $_SESSION['user_id'];
    $nume = trim($data['nume'] ?? '');
    $email = trim($data['email'] ?? '');
    $telefon = trim($data['telefon'] ?? '');
    
    // Validare
    if (empty($nume)) {
        eroare('Numele este obligatoriu');
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        eroare('Email invalid');
    }
    
    // Verifică dacă email-ul este folosit de alt utilizator
    if ($db) {
        try {
            $stmt = $db->prepare("SELECT id FROM utilizatori WHERE email = ? AND id != ?");
            $stmt->execute([$email, $userId]);
            if ($stmt->fetch()) {
                eroare('Email-ul este deja folosit de alt utilizator');
            }
            
            try {
                $stmtCol = $db->query("SHOW COLUMNS FROM utilizatori LIKE 'judet'");
                if ($stmtCol->rowCount() === 0) {
                    $db->exec("ALTER TABLE utilizatori ADD COLUMN judet VARCHAR(100) NULL AFTER telefon");
                }
            } catch(PDOException $e) {
                error_log('Eroare verificare coloana judet: ' . $e->getMessage());
            }
            
            try {
                $stmtCol = $db->query("SHOW COLUMNS FROM utilizatori LIKE 'adresa'");
                if ($stmtCol->rowCount() === 0) {
                    $db->exec("ALTER TABLE utilizatori ADD COLUMN adresa VARCHAR(255) NULL AFTER judet");
                }
            } catch(PDOException $e) {
                error_log('Eroare verificare coloana adresa: ' . $e->getMessage());
            }
            
            // Actualizează datele
            $stmt = $db->prepare("
                UPDATE utilizatori 
                SET nume = ?, email = ?, telefon = ?, judet = ?, adresa = ? 
                WHERE id = ?
            ");
            $stmt->execute([$nume, $email, $telefon, $judet ?: null, $adresa ?: null, $userId]);
            
            // Actualizează sesiunea
            $_SESSION['nume'] = $nume;
            $_SESSION['email'] = $email;
            $_SESSION['telefon'] = $telefon;
            $_SESSION['judet'] = $judet;
            $_SESSION['adresa'] = $adresa;
            
            raspuns([
                'success' => true,
                'mesaj' => 'Datele au fost actualizate cu succes!'
            ]);
        } catch(PDOException $e) {
            error_log('Eroare la actualizare profil: ' . $e->getMessage());
            eroare('Eroare la actualizarea datelor');
        }
    } else {
        eroare('Eroare de conexiune la baza de date');
    }
}

// ========== CHANGE PASSWORD ==========
else if ($method === 'POST' && isset($data['action']) && $data['action'] === 'change_password') {
    session_start();
    
    if (!isset($_SESSION['user_id'])) {
        eroare('Nu ești autentificat');
    }
    
    $userId = $_SESSION['user_id'];
    $parolaCurenta = $data['parola_curenta'] ?? '';
    $parolaNoua = $data['parola_noua'] ?? '';
    
    // Validare
    if (empty($parolaCurenta)) {
        eroare('Parola curentă este obligatorie');
    }
    
    if (empty($parolaNoua) || strlen($parolaNoua) < 6) {
        eroare('Parola nouă trebuie să aibă minimum 6 caractere');
    }
    
    if ($db) {
        try {
            // Verifică parola curentă
            $stmt = $db->prepare("SELECT parola FROM utilizatori WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            
            if (!$user || !password_verify($parolaCurenta, $user['parola'])) {
                eroare('Parola curentă este incorectă');
            }
            
            // Hash parola nouă
            $parolaNouaHash = password_hash($parolaNoua, PASSWORD_DEFAULT);
            
            // Actualizează parola
            $stmt = $db->prepare("UPDATE utilizatori SET parola = ? WHERE id = ?");
            $stmt->execute([$parolaNouaHash, $userId]);
            
            // Actualizează parola criptată pentru admin (dacă tabelul există)
            try {
                $stmt = $db->query("SHOW TABLES LIKE 'parole_admin'");
                if ($stmt->rowCount() > 0) {
                    $encryptionKey = 'marc_ro_secret_key_2024_change_this!';
                    $cipher = "AES-128-CBC";
                    $ivlen = openssl_cipher_iv_length($cipher);
                    $iv = openssl_random_pseudo_bytes($ivlen);
                    $encrypted = openssl_encrypt($parolaNoua, $cipher, $encryptionKey, 0, $iv);
                    $encryptedPassword = base64_encode($encrypted . '::' . $iv);
                    
                    $stmt = $db->prepare("
                        UPDATE parole_admin 
                        SET parola_criptata = ? 
                        WHERE id_utilizator = ?
                    ");
                    $stmt->execute([$encryptedPassword, $userId]);
                }
            } catch(PDOException $e) {
                // Ignoră eroarea dacă tabelul nu există
                error_log('Eroare la actualizare parola criptată: ' . $e->getMessage());
            }
            
            raspuns([
                'success' => true,
                'mesaj' => 'Parola a fost schimbată cu succes!'
            ]);
        } catch(PDOException $e) {
            error_log('Eroare la schimbare parolă: ' . $e->getMessage());
            eroare('Eroare la schimbarea parolei');
        }
    } else {
        eroare('Eroare de conexiune la baza de date');
    }
}

else {
    eroare('Acțiune invalidă');
}
?>

