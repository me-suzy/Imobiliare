<?php
require_once 'config.php';

$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents('php://input'), true);

// Verifică autentificare
$userId = verificaAutentificare();

// ========== VÂNZĂTORI DE EVALUAT ==========
if ($method === 'GET' && isset($_GET['action']) && $_GET['action'] === 'to_evaluate') {
    
    // Returnează lista vânzători cu care ai făcut tranzacții (pentru moment, gol)
    raspuns(['ratinguri' => []]);
}

// ========== RATINGURI ACORDATE ==========
else if ($method === 'GET' && isset($_GET['action']) && $_GET['action'] === 'given') {
    
    $stmt = $db->prepare("
        SELECT r.*, u.nume as nume_utilizator, a.titlu as titlu_anunt
        FROM ratinguri r
        JOIN utilizatori u ON r.id_utilizator_vanzator = u.id
        LEFT JOIN anunturi a ON r.id_anunt = a.id
        WHERE r.id_utilizator_cumparator = ?
        ORDER BY r.data_creare DESC
    ");
    $stmt->execute([$userId]);
    $ratinguri = $stmt->fetchAll();
    
    raspuns(['ratinguri' => $ratinguri]);
}

// ========== RATINGURI PRIMITE ==========
else if ($method === 'GET' && isset($_GET['action']) && $_GET['action'] === 'received') {
    
    $stmt = $db->prepare("
        SELECT r.*, u.nume as nume_utilizator, a.titlu as titlu_anunt
        FROM ratinguri r
        JOIN utilizatori u ON r.id_utilizator_cumparator = u.id
        LEFT JOIN anunturi a ON r.id_anunt = a.id
        WHERE r.id_utilizator_vanzator = ?
        ORDER BY r.data_creare DESC
    ");
    $stmt->execute([$userId]);
    $ratinguri = $stmt->fetchAll();
    
    raspuns(['ratinguri' => $ratinguri]);
}

// ========== ACORDĂ RATING ==========
else if ($method === 'POST' && isset($data['action']) && $data['action'] === 'create') {
    
    $idVanzator = intval($data['id_utilizator_vanzator'] ?? 0);
    $idAnunt = isset($data['id_anunt']) ? intval($data['id_anunt']) : null;
    $stele = intval($data['stele'] ?? 0);
    $comentariu = trim($data['comentariu'] ?? '');
    
    if (!$idVanzator) {
        eroare('ID vânzător invalid');
    }
    
    if ($stele < 1 || $stele > 5) {
        eroare('Ratingul trebuie să fie între 1 și 5 stele');
    }
    
    if ($idVanzator == $userId) {
        eroare('Nu poți evalua propriul tău anunț');
    }
    
    try {
        $stmt = $db->prepare("
            INSERT INTO ratinguri (id_utilizator_vanzator, id_utilizator_cumparator, id_anunt, stele, comentariu) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$idVanzator, $userId, $idAnunt, $stele, $comentariu]);
        
        raspuns([
            'success' => true,
            'mesaj' => 'Rating acordat cu succes',
            'id' => $db->lastInsertId()
        ], 201);
        
    } catch(PDOException $e) {
        eroare('Eroare la acordarea ratingului: ' . $e->getMessage(), 500);
    }
}

else {
    eroare('Acțiune invalidă');
}
?>

