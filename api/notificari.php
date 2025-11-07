<?php
require_once 'config.php';

$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents('php://input'), true);

// Verifică autentificare
$userId = verificaAutentificare();

// ========== PREIA NOTIFICĂRI ==========
if ($method === 'GET') {
    
    $citita = $_GET['citita'] ?? null;
    
    $sql = "SELECT * FROM notificari WHERE id_utilizator = ?";
    $params = [$userId];
    
    if ($citita !== null) {
        $sql .= " AND citita = ?";
        $params[] = $citita === 'true' ? 1 : 0;
    }
    
    $sql .= " ORDER BY data_creare DESC LIMIT 50";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $notificari = $stmt->fetchAll();
    
    // Numără notificările necitite
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM notificari WHERE id_utilizator = ? AND citita = FALSE");
    $stmt->execute([$userId]);
    $countNecitite = $stmt->fetch()['count'];
    
    raspuns([
        'notificari' => $notificari,
        'necitite' => (int)$countNecitite
    ]);
}

// ========== MARCAZĂ CA CITITĂ ==========
else if ($method === 'PUT' && isset($data['id'])) {
    
    $notificareId = intval($data['id']);
    
    $stmt = $db->prepare("UPDATE notificari SET citita = TRUE WHERE id = ? AND id_utilizator = ?");
    $stmt->execute([$notificareId, $userId]);
    
    raspuns(['success' => true, 'mesaj' => 'Notificare marcată ca citită']);
}

// ========== MARCAZĂ TOATE CA CITITE ==========
else if ($method === 'PUT' && isset($data['action']) && $data['action'] === 'mark_all_read') {
    
    $stmt = $db->prepare("UPDATE notificari SET citita = TRUE WHERE id_utilizator = ? AND citita = FALSE");
    $stmt->execute([$userId]);
    
    raspuns(['success' => true, 'mesaj' => 'Toate notificările marcate ca citite']);
}

// ========== ȘTERGE NOTIFICARE ==========
else if ($method === 'DELETE' && isset($_GET['id'])) {
    
    $notificareId = intval($_GET['id']);
    
    $stmt = $db->prepare("DELETE FROM notificari WHERE id = ? AND id_utilizator = ?");
    $stmt->execute([$notificareId, $userId]);
    
    raspuns(['success' => true, 'mesaj' => 'Notificare ștearsă']);
}

// ========== CREARE NOTIFICARE (ADMIN SAU SISTEM) ==========
else if ($method === 'POST' && isset($data['action']) && $data['action'] === 'create') {
    
    // Doar admin sau sistemul poate crea notificări
    $tipCont = getTipCont();
    if ($tipCont !== 'admin') {
        // Verifică dacă e pentru utilizatorul curent
        $targetUserId = intval($data['id_utilizator'] ?? 0);
        if ($targetUserId !== $userId) {
            eroare('Nu ai permisiunea să creezi notificări pentru alți utilizatori', 403);
        }
    }
    
    $targetUserId = $tipCont === 'admin' ? intval($data['id_utilizator'] ?? $userId) : $userId;
    $tip = $data['tip'] ?? 'info';
    $titlu = trim($data['titlu'] ?? '');
    $mesaj = trim($data['mesaj'] ?? '');
    $link = $data['link'] ?? null;
    
    if (empty($titlu) || empty($mesaj)) {
        eroare('Titlu și mesaj sunt obligatorii');
    }
    
    $stmt = $db->prepare("
        INSERT INTO notificari (id_utilizator, tip, titlu, mesaj, link) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$targetUserId, $tip, $titlu, $mesaj, $link]);
    
    raspuns([
        'success' => true,
        'mesaj' => 'Notificare creată',
        'id' => $db->lastInsertId()
    ], 201);
}

else {
    eroare('Metodă HTTP invalidă');
}
?>

