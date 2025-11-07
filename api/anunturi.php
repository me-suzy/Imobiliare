<?php
require_once 'config.php';

$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents('php://input'), true);

// ========== LISTĂ ANUNȚURI (PUBLIC) ==========
if ($method === 'GET' && !isset($_GET['id'])) {
    
    $categorie = $_GET['categorie'] ?? null;
    $pretMin = $_GET['pret_min'] ?? null;
    $pretMax = $_GET['pret_max'] ?? null;
    $oras = $_GET['oras'] ?? null;
    $cautare = $_GET['cautare'] ?? null;
    $limit = $_GET['limit'] ?? null;
    $pagina = max(1, intval($_GET['pagina'] ?? 1));
    $perPagina = $limit ? intval($limit) : 20;
    $offset = ($pagina - 1) * $perPagina;
    
    // Verifică dacă coloana data_activare există
    try {
        $stmtCheckCol = $db->query("SHOW COLUMNS FROM anunturi LIKE 'data_activare'");
        if ($stmtCheckCol->rowCount() == 0) {
            $db->exec("ALTER TABLE anunturi ADD COLUMN data_activare DATETIME NULL AFTER data_actualizare");
        }
    } catch(PDOException $e) {
        // Ignoră eroarea
    }
    
    // Construiește query - doar anunțuri active care nu au expirat (după 30 de zile de la activare)
    // Folosește data_activare dacă există, altfel data_creare
    $sql = "SELECT a.*, u.nume as nume_utilizator, u.telefon as telefon_utilizator,
                   COALESCE(a.data_activare, a.data_creare) as data_referinta
            FROM anunturi a 
            JOIN utilizatori u ON a.id_utilizator = u.id 
            WHERE a.status = 'activ' 
            AND COALESCE(a.data_activare, a.data_creare) >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
    
    $params = [];
    
    if ($categorie) {
        $sql .= " AND a.categorie = ?";
        $params[] = $categorie;
    }
    
    if ($pretMin) {
        $sql .= " AND a.pret >= ?";
        $params[] = $pretMin;
    }
    
    if ($pretMax) {
        $sql .= " AND a.pret <= ?";
        $params[] = $pretMax;
    }
    
    if ($oras) {
        $sql .= " AND a.oras LIKE ?";
        $params[] = "%$oras%";
    }
    
    if ($cautare) {
        $sql .= " AND (a.titlu LIKE ? OR a.descriere LIKE ?)";
        $params[] = "%$cautare%";
        $params[] = "%$cautare%";
    }
    
    $sql .= " ORDER BY a.data_creare DESC LIMIT $perPagina OFFSET $offset";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $anunturi = $stmt->fetchAll();
    
    // Procesează imaginile (convertește JSON în array)
    foreach ($anunturi as &$anunt) {
        $anunt['imagini'] = json_decode($anunt['imagini'] ?? '[]', true);
    }
    
    raspuns(['anunturi' => $anunturi]);
}

// ========== DETALII ANUNȚ (PUBLIC SAU ADMIN) ==========
else if ($method === 'GET' && isset($_GET['id'])) {
    
    $id = intval($_GET['id']);
    
    // Verifică dacă e admin sau utilizator autentificat
    $isAdmin = false;
    $userId = null;
    
    try {
        @session_start();
        if (isset($_SESSION['user_id'])) {
            $userId = $_SESSION['user_id'];
            if (isset($_SESSION['tip_cont']) && $_SESSION['tip_cont'] === 'admin') {
                $isAdmin = true;
            }
        }
    } catch (Exception $e) {
        // Ignoră erorile de sesiune
    }
    
    // Admin poate vedea orice anunț (activ sau inactiv), utilizatorii doar anunțuri active
    if ($isAdmin) {
        $stmt = $db->prepare("
            SELECT a.*, u.nume as nume_utilizator, u.telefon as telefon_utilizator, u.email as email_utilizator
            FROM anunturi a 
            JOIN utilizatori u ON a.id_utilizator = u.id 
            WHERE a.id = ? AND a.status != 'sters'
        ");
    } else {
        // Verifică dacă coloana data_activare există
        try {
            $stmtCheckCol = $db->query("SHOW COLUMNS FROM anunturi LIKE 'data_activare'");
            if ($stmtCheckCol->rowCount() == 0) {
                $db->exec("ALTER TABLE anunturi ADD COLUMN data_activare DATETIME NULL AFTER data_actualizare");
            }
        } catch(PDOException $e) {
            // Ignoră eroarea
        }
        
        // Face automat inactive anunțurile active care au expirat
        try {
            $db->exec("
                UPDATE anunturi 
                SET status = 'inactiv', data_actualizare = NOW() 
                WHERE status = 'activ' 
                AND COALESCE(data_activare, data_creare) < DATE_SUB(NOW(), INTERVAL 30 DAY)
            ");
        } catch(PDOException $e) {
            error_log('Eroare la expirare automată: ' . $e->getMessage());
        }
        
        // Public sau utilizator autentificat - doar anunțuri active care nu au expirat (după 30 de zile de la activare)
        $stmt = $db->prepare("
            SELECT a.*, u.nume as nume_utilizator, u.telefon as telefon_utilizator, u.email as email_utilizator
            FROM anunturi a 
            JOIN utilizatori u ON a.id_utilizator = u.id 
            WHERE a.id = ? 
            AND a.status = 'activ' 
            AND COALESCE(a.data_activare, a.data_creare) >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
    }
    
    $stmt->execute([$id]);
    $anunt = $stmt->fetch();
    
    if (!$anunt) {
        eroare('Anunț negăsit', 404);
    }
    
    // Incrementează vizualizări doar dacă nu e admin (admin-ul nu ar trebui să afecteze statisticile)
    if (!$isAdmin) {
        $stmt = $db->prepare("UPDATE anunturi SET vizualizari = vizualizari + 1 WHERE id = ?");
        $stmt->execute([$id]);
    }
    
    $anunt['imagini'] = json_decode($anunt['imagini'] ?? '[]', true);
    
    raspuns(['anunt' => $anunt]);
}

// ========== CREARE ANUNȚ (AUTENTIFICAT) ==========
else if ($method === 'POST' && !isset($data['id'])) {
    
    $userId = verificaAutentificare();
    
    $titlu = trim($data['titlu'] ?? '');
    $descriere = trim($data['descriere'] ?? '');
    $categorie = trim($data['categorie'] ?? '');
    $pret = floatval($data['pret'] ?? 0);
    $moneda = $data['moneda'] ?? 'RON';
    $oras = trim($data['oras'] ?? '');
    $judet = trim($data['judet'] ?? '');
    $imagini = $data['imagini'] ?? [];
    
    // Validare
    if (empty($titlu) || empty($descriere) || empty($categorie)) {
        eroare('Titlu, descriere și categorie sunt obligatorii');
    }
    
    if ($pret < 0) {
        eroare('Prețul nu poate fi negativ');
    }
    
    try {
        // Status inițial: 'inactiv' (va apărea în "În așteptare")
        // Admin-ul poate aproba anunțul și-l va marca ca 'activ'
        $status = 'inactiv';
        
        $stmt = $db->prepare("
            INSERT INTO anunturi (id_utilizator, titlu, descriere, categorie, pret, moneda, oras, judet, imagini, status, data_creare) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $userId,
            $titlu,
            $descriere,
            $categorie,
            $pret,
            $moneda,
            $oras,
            $judet,
            json_encode($imagini, JSON_UNESCAPED_UNICODE),
            $status
        ]);
        
        $anuntId = $db->lastInsertId();
        
        // Salvează informațiile despre imagini în tabel (dacă există)
        if (!empty($imagini)) {
            try {
                // Verifică dacă tabelul imagini_anunturi există
                $db->exec("
                    CREATE TABLE IF NOT EXISTS imagini_anunturi (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        id_anunt INT NOT NULL,
                        id_utilizator INT NOT NULL,
                        cale_imagine VARCHAR(255) NOT NULL,
                        nume_fisier VARCHAR(255) NOT NULL,
                        ordine INT DEFAULT 1,
                        data_creare DATETIME DEFAULT CURRENT_TIMESTAMP,
                        FOREIGN KEY (id_anunt) REFERENCES anunturi(id) ON DELETE CASCADE,
                        FOREIGN KEY (id_utilizator) REFERENCES utilizatori(id) ON DELETE CASCADE,
                        INDEX idx_anunt (id_anunt),
                        INDEX idx_utilizator (id_utilizator)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ");
                
                // Inserează fiecare imagine
                $stmtImg = $db->prepare("
                    INSERT INTO imagini_anunturi (id_anunt, id_utilizator, cale_imagine, nume_fisier, ordine) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                
                foreach ($imagini as $index => $imagineUrl) {
                    $numeFisier = basename($imagineUrl);
                    $stmtImg->execute([
                        $anuntId,
                        $userId,
                        $imagineUrl,
                        $numeFisier,
                        $index + 1
                    ]);
                }
            } catch(PDOException $e) {
                // Loghează eroarea dar nu opri procesul
                error_log('Eroare la salvare imagini în tabel: ' . $e->getMessage());
            }
        }
        
        raspuns([
            'success' => true,
            'mesaj' => 'Anunț publicat cu succes! Va fi activat după aprobare.',
            'id' => $anuntId,
            'status' => $status
        ], 201);
        
    } catch(PDOException $e) {
        error_log('Eroare la creare anunț: ' . $e->getMessage());
        eroare('Eroare la creare anunț: ' . $e->getMessage(), 500);
    }
}

// ========== ACTUALIZARE ANUNȚ (AUTENTIFICAT) ==========
else if ($method === 'PUT' && isset($data['id'])) {
    
    $userId = verificaAutentificare();
    $anuntId = intval($data['id']);
    
    // Verifică că anunțul aparține utilizatorului
    $stmt = $db->prepare("SELECT id_utilizator FROM anunturi WHERE id = ?");
    $stmt->execute([$anuntId]);
    $anunt = $stmt->fetch();
    
    if (!$anunt || $anunt['id_utilizator'] != $userId) {
        eroare('Nu ai permisiunea să modifici acest anunț', 403);
    }
    
    // Dacă este reactualizare, resetează data creării (pentru a prelungi validitatea)
    if (isset($data['reactualizare']) && $data['reactualizare'] === true) {
        try {
            $stmt = $db->prepare("
                UPDATE anunturi 
                SET data_creare = NOW(), data_actualizare = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$anuntId]);
            
            raspuns(['success' => true, 'mesaj' => 'Anunț reactualizat cu succes!']);
            return;
        } catch(PDOException $e) {
            eroare('Eroare la reactualizare anunț: ' . $e->getMessage(), 500);
        }
    }
    
    // Actualizare normală
    $titlu = trim($data['titlu'] ?? '');
    $descriere = trim($data['descriere'] ?? '');
    $pret = floatval($data['pret'] ?? 0);
    $status = $data['status'] ?? null;
    $dezactivatManual = isset($data['dezactivat_manual']) && $data['dezactivat_manual'] === true;
    
    $updates = [];
    $params = [];
    
    if (!empty($titlu)) {
        $updates[] = "titlu = ?";
        $params[] = $titlu;
    }
    
    if (!empty($descriere)) {
        $updates[] = "descriere = ?";
        $params[] = $descriere;
    }
    
    if ($pret > 0) {
        $updates[] = "pret = ?";
        $params[] = $pret;
    }
    
    if ($status && in_array($status, ['activ', 'inactiv', 'vandut'])) {
        $updates[] = "status = ?";
        $params[] = $status;
        
        // Dacă se dezactivează manual un anunț care a fost activat, păstrăm data_activare
        // pentru ca anunțul să apară în "Expirate" (nu în "În așteptare")
        if ($status === 'inactiv' && $dezactivatManual) {
            // Verifică dacă anunțul a fost activat înainte
            $stmtCheck = $db->prepare("SELECT data_activare FROM anunturi WHERE id = ?");
            $stmtCheck->execute([$anuntId]);
            $anuntCheck = $stmtCheck->fetch();
            
            // Dacă nu există data_activare, o setăm acum (pentru a marca că a fost activat înainte)
            if (!$anuntCheck['data_activare']) {
                // Verifică dacă coloana data_activare există
                try {
                    $stmtCheckCol = $db->query("SHOW COLUMNS FROM anunturi LIKE 'data_activare'");
                    if ($stmtCheckCol->rowCount() == 0) {
                        $db->exec("ALTER TABLE anunturi ADD COLUMN data_activare DATETIME NULL AFTER data_actualizare");
                    }
                } catch(PDOException $e) {
                    // Ignoră eroarea
                }
                
                // Setează data_activare la data creării (sau data actualizării) pentru a marca că a fost activat
                $updates[] = "data_activare = COALESCE(data_activare, data_creare)";
            }
        }
    }
    
    if (empty($updates)) {
        eroare('Nu s-au specificat câmpuri pentru actualizare');
    }
    
    $updates[] = "data_actualizare = NOW()";
    $params[] = $anuntId;
    
    try {
        $sql = "UPDATE anunturi SET " . implode(', ', $updates) . " WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        
        raspuns(['success' => true, 'mesaj' => 'Anunț actualizat cu succes!']);
        
    } catch(PDOException $e) {
        eroare('Eroare la actualizare anunț: ' . $e->getMessage(), 500);
    }
}

// ========== ȘTERGERE ANUNȚ (AUTENTIFICAT) ==========
else if ($method === 'DELETE' && isset($_GET['id'])) {
    
    $userId = verificaAutentificare();
    $anuntId = intval($_GET['id']);
    
    // Verifică că anunțul aparține utilizatorului
    $stmt = $db->prepare("SELECT id_utilizator FROM anunturi WHERE id = ?");
    $stmt->execute([$anuntId]);
    $anunt = $stmt->fetch();
    
    if (!$anunt || $anunt['id_utilizator'] != $userId) {
        eroare('Nu ai permisiunea să ștergi acest anunț', 403);
    }
    
    try {
        // Ștergere soft (marchează ca șters, nu șterge din DB)
        $stmt = $db->prepare("UPDATE anunturi SET status = 'sters' WHERE id = ?");
        $stmt->execute([$anuntId]);
        
        raspuns(['success' => true, 'mesaj' => 'Anunț șters cu succes!']);
        
    } catch(PDOException $e) {
        eroare('Eroare la ștergere anunț: ' . $e->getMessage(), 500);
    }
}

else {
    eroare('Metodă HTTP invalidă');
}
?>

