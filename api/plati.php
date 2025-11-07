<?php
require_once 'config.php';

$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents('php://input'), true);

// Verifică autentificare
$userId = verificaAutentificare();

// ========== PREIA PLĂȚI ==========
if ($method === 'GET') {
    
    $status = $_GET['status'] ?? null;
    
    $sql = "SELECT p.*, a.titlu as titlu_anunt 
            FROM plati p
            LEFT JOIN anunturi a ON p.id_anunt = a.id
            WHERE p.id_utilizator = ?";
    $params = [$userId];
    
    if ($status && $status !== 'all') {
        $sql .= " AND p.status = ?";
        $params[] = $status;
    }
    
    $sql .= " ORDER BY p.data_creare DESC LIMIT 100";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $plati = $stmt->fetchAll();
    
    // Preia informații utilizator
    $stmt = $db->prepare("SELECT sold_cont, credite_disponibile FROM utilizatori WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    // Adaugă titlu pentru plăți fără anunț
    foreach ($plati as &$plata) {
        if (!$plata['titlu_anunt']) {
            $plata['titlu'] = 'Plată ' . $plata['tip'];
        } else {
            $plata['titlu'] = $plata['titlu_anunt'];
        }
    }
    
    raspuns([
        'plati' => $plati,
        'user' => [
            'sold_cont' => floatval($user['sold_cont'] ?? 0),
            'credite_disponibile' => intval($user['credite_disponibile'] ?? 0)
        ]
    ]);
}

// ========== CREARE PLATĂ ==========
else if ($method === 'POST' && isset($data['action']) && $data['action'] === 'create') {
    
    $tip = $data['tip'] ?? '';
    $valoare = floatval($data['valoare'] ?? 0);
    $moneda = $data['moneda'] ?? 'EUR';
    $idAnunt = isset($data['id_anunt']) ? intval($data['id_anunt']) : null;
    
    if (empty($tip) || $valoare <= 0) {
        eroare('Tip și valoare invalide');
    }
    
    if (!in_array($tip, ['promovare', 'pachet', 'credit'])) {
        eroare('Tip plată invalid');
    }
    
    try {
        $idTranzactie = 'TXN' . time() . rand(1000, 9999);
        
        $stmt = $db->prepare("
            INSERT INTO plati (id_utilizator, id_anunt, tip, valoare, moneda, status, id_tranzactie) 
            VALUES (?, ?, ?, ?, ?, 'pending', ?)
        ");
        $stmt->execute([$userId, $idAnunt, $tip, $valoare, $moneda, $idTranzactie]);
        
        $plataId = $db->lastInsertId();
        
        raspuns([
            'success' => true,
            'mesaj' => 'Plată creată',
            'id' => $plataId,
            'id_tranzactie' => $idTranzactie
        ], 201);
        
    } catch(PDOException $e) {
        eroare('Eroare la crearea plății: ' . $e->getMessage(), 500);
    }
}

else {
    eroare('Acțiune invalidă');
}
?>

