<?php
require_once 'config.php';

$db = getDB();
if (!$db) {
    eroare('Eroare de conexiune la baza de date', 500);
}

session_start();
if (!isset($_SESSION['user_id'])) {
    eroare('Trebuie să fii autentificat pentru a trimite un mesaj de contact', 401);
}

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    $data = $_POST;
}

$subiect = trim($data['subiect'] ?? '');
$mesaj = trim($data['mesaj'] ?? '');

if ($subiect === '' || $mesaj === '') {
    eroare('Completează subiectul și mesajul.');
}

$userId = (int)$_SESSION['user_id'];

try {
    // Găsește un administrator
    $stmt = $db->query("SELECT id FROM utilizatori WHERE tip_cont = 'admin' ORDER BY id ASC LIMIT 1");
    $admin = $stmt->fetch();

    if (!$admin) {
        eroare('Nu există un cont de administrator configurat.', 500);
    }

    $adminId = (int)$admin['id'];

    if ($adminId === $userId) {
        eroare('Pentru a contacta administratorul, folosește un cont de utilizator diferit.', 400);
    }

    $db->beginTransaction();

    $stmtConv = $db->prepare("
        SELECT id FROM conversatii
        WHERE (id_utilizator_initiator = ? AND id_utilizator_partener = ?)
           OR (id_utilizator_initiator = ? AND id_utilizator_partener = ?)
        LIMIT 1
    ");
    $stmtConv->execute([$userId, $adminId, $adminId, $userId]);
    $conversatie = $stmtConv->fetch();

    if ($conversatie) {
        $conversatieId = (int)$conversatie['id'];
        // Actualizăm subiectul dacă este gol
        $stmtUpdate = $db->prepare("UPDATE conversatii SET subiect = COALESCE(NULLIF(subiect, ''), ?), updated_at = NOW() WHERE id = ?");
        $stmtUpdate->execute([$subiect, $conversatieId]);
    } else {
        $stmtInsertConv = $db->prepare("
            INSERT INTO conversatii (id_anunt, id_utilizator_initiator, id_utilizator_partener, subiect, ultimul_mesaj)
            VALUES (NULL, ?, ?, ?, NOW())
        ");
        $stmtInsertConv->execute([$userId, $adminId, $subiect]);
        $conversatieId = (int)$db->lastInsertId();
    }

    $stmtMsg = $db->prepare("
        INSERT INTO mesaje (id_conversatie, id_expeditor, mesaj)
        VALUES (?, ?, ?)
    ");
    $stmtMsg->execute([$conversatieId, $userId, $mesaj]);

    $db->commit();

    raspuns([
        'success' => true,
        'mesaj' => 'Mesajul a fost trimis către administrator.',
        'conversatie_id' => $conversatieId
    ]);
} catch (PDOException $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    error_log('Eroare contact admin: ' . $e->getMessage());
    eroare('Nu am putut trimite mesajul. Încearcă din nou.', 500);
}

