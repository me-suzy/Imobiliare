<?php
require_once 'config.php';

$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];

// Verifică autentificare
$userId = verificaAutentificare();

// ========== NUMĂRĂ ANUNȚURI PE STATUS ==========
if ($method === 'GET' && isset($_GET['action']) && $_GET['action'] === 'count') {
    
    // Verifică dacă coloana data_activare există
    try {
        $stmtCheckCol = $db->query("SHOW COLUMNS FROM anunturi LIKE 'data_activare'");
        if ($stmtCheckCol->rowCount() == 0) {
            $db->exec("ALTER TABLE anunturi ADD COLUMN data_activare DATETIME NULL AFTER data_actualizare");
        }
    } catch(PDOException $e) {
        // Ignoră eroarea
    }
    
    // Verifică și face automat inactive anunțurile care au expirat
    try {
        $stmtExpire = $db->prepare("
            UPDATE anunturi 
            SET status = 'inactiv', data_actualizare = NOW() 
            WHERE id_utilizator = ? 
            AND status = 'activ' 
            AND COALESCE(data_activare, data_creare) < DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
        $stmtExpire->execute([$userId]);
    } catch(PDOException $e) {
        error_log('Eroare la expirare automată: ' . $e->getMessage());
    }
    
    $stmt = $db->prepare("
        SELECT 
            -- Active: status = 'activ' și nu au expirat (după 30 de zile de la activare)
            SUM(CASE WHEN status = 'activ' AND COALESCE(data_activare, data_creare) >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as active,
            -- Pending: status = 'inactiv' și NU a fost activat niciodată (nu există data_activare) - în așteptare de aprobare
            SUM(CASE WHEN status = 'inactiv' AND data_activare IS NULL THEN 1 ELSE 0 END) as pending,
            -- Sold: status = 'vandut'
            SUM(CASE WHEN status = 'vandut' THEN 1 ELSE 0 END) as sold,
            -- Expired: anunțuri care au fost activate dar acum sunt inactive (expirate sau dezactivate manual)
            -- Include: anunțuri cu data_activare setată dar status = 'inactiv' (au fost activate, acum sunt inactive)
            -- SAU anunțuri care au expirat (după 30 de zile de la activare)
            SUM(CASE WHEN (
                (status = 'inactiv' AND data_activare IS NOT NULL) OR 
                (COALESCE(data_activare, data_creare) < DATE_SUB(NOW(), INTERVAL 30 DAY) AND status != 'sters')
            ) THEN 1 ELSE 0 END) as expired
        FROM anunturi 
        WHERE id_utilizator = ? AND status != 'sters'
    ");
    $stmt->execute([$userId]);
    $counts = $stmt->fetch();
    
    raspuns([
        'active' => (int)$counts['active'],
        'pending' => (int)$counts['pending'],
        'sold' => (int)$counts['sold'],
        'expired' => (int)$counts['expired']
    ]);
    exit;
}

// ========== PREIA ANUNȚURILE UTILIZATORULUI ==========
if ($method === 'GET') {
    
    $status = $_GET['status'] ?? null;
    
    // Construiește query
    $sql = "SELECT * FROM anunturi WHERE id_utilizator = ?";
    $params = [$userId];
    
    if ($status) {
        // Mapare status din frontend la status din DB
        if ($status === 'active') {
            $sql .= " AND status = 'activ'";
        } else if ($status === 'pending') {
            // Anunțuri în așteptare = status = 'inactiv' și NU a fost activat niciodată (nu există data_activare)
            $sql .= " AND status = 'inactiv' AND data_activare IS NULL";
        } else if ($status === 'sold') {
            $sql .= " AND status = 'vandut'";
        } else if ($status === 'expired') {
            // Expirate = anunțuri care au fost activate dar acum sunt inactive (expirate sau dezactivate manual)
            // SAU anunțuri care au expirat (după 30 de zile de la activare)
            $sql .= " AND ((status = 'inactiv' AND data_activare IS NOT NULL) OR (COALESCE(data_activare, data_creare) < DATE_SUB(NOW(), INTERVAL 30 DAY) AND status != 'sters'))";
        }
    }
    
    $sql .= " ORDER BY data_creare DESC";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $anunturi = $stmt->fetchAll();
    
    // Procesează imaginile
    foreach ($anunturi as &$anunt) {
        $anunt['imagini'] = json_decode($anunt['imagini'] ?? '[]', true);
    }
    
    raspuns(['anunturi' => $anunturi]);
} else {
    eroare('Metodă HTTP invalidă');
}
?>

