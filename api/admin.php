<?php
require_once 'config.php';

$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents('php://input'), true);

// Verifică că e admin
$adminId = verificaAdmin();

// ========== DASHBOARD ADMIN ==========
if ($method === 'GET' && isset($_GET['action']) && $_GET['action'] === 'dashboard') {
    
    try {
        // Statistici generale
        $stats = [];
        
        // Total utilizatori
        $stmt = $db->query("SELECT COUNT(*) as total FROM utilizatori");
        $stats['total_utilizatori'] = (int)$stmt->fetch()['total'];
        
        // Total anunțuri
        $stmt = $db->query("SELECT COUNT(*) as total FROM anunturi");
        $stats['total_anunturi'] = (int)$stmt->fetch()['total'];
        
        // Anunțuri active
        $stmt = $db->query("SELECT COUNT(*) as total FROM anunturi WHERE status = 'activ'");
        $stats['anunturi_active'] = (int)$stmt->fetch()['total'];
        
        // Anunțuri noi azi
        $stmt = $db->query("SELECT COUNT(*) as total FROM anunturi WHERE DATE(data_creare) = CURDATE()");
        $stats['anunturi_noi_azi'] = (int)$stmt->fetch()['total'];
        
        // Utilizatori noi azi
        $stmt = $db->query("SELECT COUNT(*) as total FROM utilizatori WHERE DATE(data_creare) = CURDATE()");
        $stats['utilizatori_noi_azi'] = (int)$stmt->fetch()['total'];
        
        // Total mesaje
        $stmt = $db->query("SELECT COUNT(*) as total FROM mesaje");
        $stats['total_mesaje'] = (int)$stmt->fetch()['total'];
        
        // Total plăți (din tabelul plati)
        $stmt = $db->query("SELECT COUNT(*) as total, SUM(valoare) as suma FROM plati WHERE status = 'completed'");
        $paymentData = $stmt->fetch();
        $stats['total_plati'] = (int)$paymentData['total'];
        
        // Suma totală plăți = suma soldurilor utilizatorilor (sold_cont)
        $stmt = $db->query("SELECT SUM(sold_cont) as suma_solduri FROM utilizatori");
        $soldData = $stmt->fetch();
        $stats['suma_totala_plati'] = floatval($soldData['suma_solduri'] ?? 0);
        
        raspuns(['stats' => $stats]);
        
    } catch(PDOException $e) {
        eroare('Eroare la preluarea statisticilor: ' . $e->getMessage(), 500);
    }
}

// ========== GESTIONARE UTILIZATORI ==========
else if ($method === 'GET' && isset($_GET['action']) && $_GET['action'] === 'utilizatori') {
    
    $pagina = max(1, intval($_GET['pagina'] ?? 1));
    $perPagina = 50;
    $offset = ($pagina - 1) * $perPagina;
    
    $stmt = $db->prepare("SELECT id, nume, email, telefon, tip_cont, sold_cont, credite_disponibile, data_creare FROM utilizatori ORDER BY data_creare DESC LIMIT ? OFFSET ?");
    $stmt->execute([$perPagina, $offset]);
    $utilizatori = $stmt->fetchAll();
    
    raspuns(['utilizatori' => $utilizatori]);
}

// ========== ACTUALIZARE UTILIZATOR (ADMIN) ==========
else if ($method === 'PUT' && isset($data['id'])) {
    
    $userId = intval($data['id']);
    $updates = [];
    $params = [];
    
    // Permite actualizarea tuturor câmpurilor
    if (isset($data['nume'])) {
        $updates[] = "nume = ?";
        $params[] = trim($data['nume']);
    }
    
    if (isset($data['email'])) {
        // Verifică dacă email-ul e valid și nu e deja folosit de alt utilizator
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            eroare('Email invalid');
        }
        $stmt = $db->prepare("SELECT id FROM utilizatori WHERE email = ? AND id != ?");
        $stmt->execute([$data['email'], $userId]);
        if ($stmt->fetch()) {
            eroare('Email-ul este deja folosit de alt utilizator');
        }
        $updates[] = "email = ?";
        $params[] = trim($data['email']);
    }
    
    if (isset($data['telefon'])) {
        $updates[] = "telefon = ?";
        $params[] = trim($data['telefon']);
    }
    
    if (isset($data['tip_cont'])) {
        $updates[] = "tip_cont = ?";
        $params[] = $data['tip_cont'];
    }
    
    if (isset($data['sold_cont'])) {
        $updates[] = "sold_cont = ?";
        $params[] = floatval($data['sold_cont']);
    }
    
    if (isset($data['credite_disponibile'])) {
        $updates[] = "credite_disponibile = ?";
        $params[] = intval($data['credite_disponibile']);
    }
    
    if (empty($updates)) {
        eroare('Nu s-au specificat câmpuri pentru actualizare');
    }
    
    $params[] = $userId;
    $sql = "UPDATE utilizatori SET " . implode(', ', $updates) . " WHERE id = ?";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    
    raspuns(['success' => true, 'mesaj' => 'Utilizator actualizat']);
}

// ========== ȘTERGE UTILIZATOR (ADMIN) ==========
else if ($method === 'DELETE' && isset($_GET['id'])) {
    
    $userId = intval($_GET['id']);
    
    // Nu permite ștergerea contului admin propriu
    if ($userId == $adminId) {
        eroare('Nu poți șterge contul tău propriu', 400);
    }
    
    $stmt = $db->prepare("DELETE FROM utilizatori WHERE id = ?");
    $stmt->execute([$userId]);
    
    raspuns(['success' => true, 'mesaj' => 'Utilizator șters']);
}

// ========== Sesiuni logare (ADMIN) ==========
else if ($method === 'GET' && isset($_GET['action']) && $_GET['action'] === 'sesiuni') {
    
    $pagina = max(1, intval($_GET['pagina'] ?? 1));
    $perPagina = 50;
    $offset = ($pagina - 1) * $perPagina;
    
    $stmt = $db->prepare("
        SELECT s.id, s.id_utilizator, s.ip_address, s.user_agent, s.metoda_autentificare, 
               s.data_logare, s.data_logout, s.activ,
               u.nume, u.email 
        FROM sesiuni_logare s
        JOIN utilizatori u ON s.id_utilizator = u.id
        ORDER BY s.data_logare DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$perPagina, $offset]);
    $sesiuni = $stmt->fetchAll();
    
    // Formatează datele pentru a fi returnate corect
    foreach ($sesiuni as &$sesiune) {
        $sesiune['data_logare'] = $sesiune['data_logare'] ? date('Y-m-d H:i:s', strtotime($sesiune['data_logare'])) : null;
        $sesiune['data_logout'] = $sesiune['data_logout'] ? date('Y-m-d H:i:s', strtotime($sesiune['data_logout'])) : null;
    }
    
    // Numără total
    $stmt = $db->query("SELECT COUNT(*) as total FROM sesiuni_logare");
    $total = $stmt->fetch()['total'];
    
    raspuns([
        'sesiuni' => $sesiuni,
        'total' => (int)$total,
        'pagina' => $pagina,
        'per_pagina' => $perPagina
    ]);
}

// ========== NUMĂRĂRI ANUNȚURI (ADMIN) ==========
else if ($method === 'GET' && isset($_GET['action']) && $_GET['action'] === 'anunturi-counts') {
    
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
    
    // Calculează numărările pentru fiecare status
    $stmt = $db->query("
        SELECT 
            COUNT(*) as total_toate,
            -- Pending: inactive care NU au fost activate niciodată (nu există data_activare)
            SUM(CASE WHEN status = 'inactiv' AND data_activare IS NULL THEN 1 ELSE 0 END) as pending,
            -- Active: active care nu au expirat
            SUM(CASE WHEN status = 'activ' AND COALESCE(data_activare, data_creare) >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as active,
            -- Expired: inactive care au fost activate (expirate sau dezactivate manual) SAU au expirat
            SUM(CASE WHEN (
                (status = 'inactiv' AND data_activare IS NOT NULL) OR 
                (COALESCE(data_activare, data_creare) < DATE_SUB(NOW(), INTERVAL 30 DAY) AND status != 'sters')
            ) THEN 1 ELSE 0 END) as expired
        FROM anunturi 
        WHERE status != 'sters'
    ");
    
    $counts = $stmt->fetch();
    
    raspuns([
        'total_toate' => (int)$counts['total_toate'],
        'pending' => (int)$counts['pending'],
        'active' => (int)$counts['active'],
        'expired' => (int)$counts['expired']
    ]);
}

// ========== GESTIONARE ANUNȚURI (ADMIN) ==========
else if ($method === 'GET' && isset($_GET['action']) && $_GET['action'] === 'anunturi') {
    
    $status = $_GET['status'] ?? null;
    $pagina = max(1, intval($_GET['pagina'] ?? 1));
    $perPagina = 50;
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
    
    // Face automat inactive toate anunțurile active care au expirat
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
    
    $sql = "SELECT a.*, u.nume as nume_utilizator, u.email as email_utilizator, u.telefon as telefon_utilizator
            FROM anunturi a 
            JOIN utilizatori u ON a.id_utilizator = u.id
            WHERE a.status != 'sters'";
    $params = [];
    
    if ($status) {
        if ($status === 'pending') {
            // În așteptare = inactive care NU au fost activate niciodată (nu există data_activare)
            $sql .= " AND a.status = 'inactiv' AND a.data_activare IS NULL";
        } else if ($status === 'active') {
            // Active = active care nu au expirat
            $sql .= " AND a.status = 'activ' AND COALESCE(a.data_activare, a.data_creare) >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        } else if ($status === 'expired') {
            // Expirate = inactive care au fost activate (expirate sau dezactivate manual) SAU au expirat
            $sql .= " AND ((a.status = 'inactiv' AND a.data_activare IS NOT NULL) OR (COALESCE(a.data_activare, a.data_creare) < DATE_SUB(NOW(), INTERVAL 30 DAY)))";
        } else {
            $sql .= " AND a.status = ?";
            $params[] = $status;
        }
    }
    // Dacă nu e status specificat (tab "Toate"), afișează TOATE anunțurile (activ, inactiv, etc.)
    
    // Ordonează după data creării (cele mai noi primele)
    $sql .= " ORDER BY a.data_creare DESC LIMIT ? OFFSET ?";
    $params[] = $perPagina;
    $params[] = $offset;
    
    try {
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $anunturi = $stmt->fetchAll();
    } catch(PDOException $e) {
        eroare('Eroare la încărcarea anunțurilor: ' . $e->getMessage(), 500);
    }
    
    // Procesează anunțurile returnate
    foreach ($anunturi as &$anunt) {
        $anunt['imagini'] = json_decode($anunt['imagini'] ?? '[]', true);
        
        // Verifică dacă anunțul a expirat (după 30 de zile de la activare)
        $dataReferinta = $anunt['data_activare'] ?? $anunt['data_creare'];
        $dataReferintaObj = strtotime($dataReferinta);
        $acum = time();
        $zileDiferenta = ($acum - $dataReferintaObj) / (60 * 60 * 24);
        
        $anunt['expirat'] = $zileDiferenta > 30;
        $anunt['zile_ramase'] = max(0, 30 - floor($zileDiferenta));
    }
    
    // Numără total pentru statusul curent
    $countSql = "SELECT COUNT(*) as total FROM anunturi WHERE status != 'sters'";
    $countParams = [];
    if ($status === 'pending') {
        $countSql .= " AND status = 'inactiv' AND data_activare IS NULL";
    } else if ($status === 'active') {
        $countSql .= " AND status = 'activ' AND COALESCE(data_activare, data_creare) >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
    } else if ($status === 'expired') {
        $countSql .= " AND ((status = 'inactiv' AND data_activare IS NOT NULL) OR (COALESCE(data_activare, data_creare) < DATE_SUB(NOW(), INTERVAL 30 DAY)))";
    } else if ($status) {
        $countSql .= " AND status = ?";
        $countParams[] = $status;
    }

    try {
        $countStmt = $db->prepare($countSql);
        $countStmt->execute($countParams);
        $total = $countStmt->fetch()['total'] ?? 0;
    } catch(PDOException $e) {
        eroare('Eroare la calcularea numărului de anunțuri: ' . $e->getMessage(), 500);
    }
    
    // Obține numărările pentru toate tab-urile
    try {
        $stmtCounts = $db->query("
            SELECT 
                COUNT(*) as total_toate,
                SUM(CASE WHEN status = 'inactiv' AND data_activare IS NULL THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'activ' AND COALESCE(data_activare, data_creare) >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN (
                    (status = 'inactiv' AND data_activare IS NOT NULL) OR 
                    (COALESCE(data_activare, data_creare) < DATE_SUB(NOW(), INTERVAL 30 DAY))
                ) THEN 1 ELSE 0 END) as expired
            FROM anunturi 
            WHERE status != 'sters'
        ");
        $counts = $stmtCounts->fetch();
    } catch(PDOException $e) {
        eroare('Eroare la calcularea statistiiților pentru tab-uri: ' . $e->getMessage(), 500);
    }
    
    raspuns([
        'anunturi' => $anunturi,
        'total' => (int)$total,
        'pagina' => $pagina,
        'per_pagina' => $perPagina,
        'counts' => [
            'toate' => (int)$counts['total_toate'],
            'pending' => (int)$counts['pending'],
            'active' => (int)$counts['active'],
            'expired' => (int)$counts['expired']
        ]
    ]);
}

// ========== ACTUALIZARE ANUNȚ (ADMIN) ==========
else if ($method === 'PUT' && isset($data['anunt_id'])) {
    
    $anuntId = intval($data['anunt_id']);
    $updates = [];
    $params = [];
    
    if (isset($data['status'])) {
        $updates[] = "status = ?";
        $params[] = $data['status'];
        
        // Dacă se aprobă anunțul (se schimbă în 'activ'), setăm data_activare
        if ($data['status'] === 'activ') {
            // Verifică dacă anunțul nu era deja activ (prima activare)
            $stmtCheck = $db->prepare("SELECT status, data_activare FROM anunturi WHERE id = ?");
            $stmtCheck->execute([$anuntId]);
            $anunt = $stmtCheck->fetch();
            
            // Dacă nu există data_activare (prima dată când se activează), o setăm
            if (!$anunt['data_activare'] || $anunt['status'] !== 'activ') {
                // Verifică dacă coloana data_activare există
                try {
                    $stmtCheckCol = $db->query("SHOW COLUMNS FROM anunturi LIKE 'data_activare'");
                    if ($stmtCheckCol->rowCount() == 0) {
                        // Creează coloana data_activare dacă nu există
                        $db->exec("ALTER TABLE anunturi ADD COLUMN data_activare DATETIME NULL AFTER data_actualizare");
                    }
                } catch(PDOException $e) {
                    // Ignoră eroarea dacă coloana există deja
                }
                
                $updates[] = "data_activare = NOW()";
            }
        }
    }
    
    // Actualizează mereu data_actualizare
    $updates[] = "data_actualizare = NOW()";
    
    if (empty($updates)) {
        eroare('Nu s-au specificat câmpuri pentru actualizare');
    }
    
    $params[] = $anuntId;
    $sql = "UPDATE anunturi SET " . implode(', ', $updates) . " WHERE id = ?";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    
    raspuns(['success' => true, 'mesaj' => 'Anunț actualizat']);
}

// ========== GESTIONARE PLĂȚI (ADMIN) ==========
else if ($method === 'GET' && isset($_GET['action']) && $_GET['action'] === 'plati') {
    
    $status = $_GET['status'] ?? null;
    $pagina = max(1, intval($_GET['pagina'] ?? 1));
    $perPagina = 50;
    $offset = ($pagina - 1) * $perPagina;
    
    // Construiește query-ul cu filtre
    $whereConditions = [];
    $params = [];
    
    if ($status) {
        $whereConditions[] = "p.status = ?";
        $params[] = $status;
    }
    
    $whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";
    
    $sql = "SELECT p.*, 
                   u.nume as nume_utilizator, 
                   u.email as email_utilizator,
                   u.telefon as telefon_utilizator,
                   a.titlu as titlu_anunt,
                   a.id as id_anunt
            FROM plati p
            LEFT JOIN utilizatori u ON p.id_utilizator = u.id
            LEFT JOIN anunturi a ON p.id_anunt = a.id
            $whereClause
            ORDER BY p.data_creare DESC 
            LIMIT ? OFFSET ?";
    
    $params[] = $perPagina;
    $params[] = $offset;
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $plati = $stmt->fetchAll();
    
    // Formatează datele
    foreach ($plati as &$plata) {
        $plata['data_creare'] = $plata['data_creare'] ? date('Y-m-d H:i:s', strtotime($plata['data_creare'])) : null;
        $plata['data_actualizare'] = $plata['data_actualizare'] ? date('Y-m-d H:i:s', strtotime($plata['data_actualizare'])) : null;
    }
    
    // Numără total
    $countSql = "SELECT COUNT(*) as total FROM plati";
    if ($status) {
        $countSql .= " WHERE status = ?";
        $countStmt = $db->prepare($countSql);
        $countStmt->execute([$status]);
    } else {
        $countStmt = $db->query($countSql);
    }
    $total = $countStmt->fetch()['total'];
    
    raspuns([
        'plati' => $plati,
        'total' => (int)$total,
        'pagina' => $pagina,
        'per_pagina' => $perPagina
    ]);
}

else {
    eroare('Acțiune invalidă');
}
?>

