<?php
require_once 'config.php';

$db = getDB();
if (!$db) {
    eroare('Eroare de conexiune la baza de date', 500);
}

function ensureChatTables(PDO $db)
{
    static $initialized = false;
    if ($initialized) {
        return;
    }

    try {
        // Verifică dacă tabelele există deja
        $stmt = $db->query("SHOW TABLES LIKE 'conversatii'");
        $conversatiiExists = $stmt->rowCount() > 0;
        
        $stmt = $db->query("SHOW TABLES LIKE 'mesaje'");
        $mesajeExists = $stmt->rowCount() > 0;
        
        // Dacă ambele tabele există, nu mai facem nimic
        if ($conversatiiExists && $mesajeExists) {
            $initialized = true;
            return;
        }
        
        // Altfel, creăm doar ce lipsește (fără constraint-uri duplicate)
        if (!$conversatiiExists) {
            $db->exec("
                CREATE TABLE conversatii (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    id_anunt INT NULL,
                    id_utilizator_initiator INT NOT NULL,
                    id_utilizator_partener INT NOT NULL,
                    subiect VARCHAR(255) NULL,
                    ultimul_mesaj DATETIME NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_initiator (id_utilizator_initiator),
                    INDEX idx_partener (id_utilizator_partener)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        }

        if (!$mesajeExists) {
            $db->exec("
                CREATE TABLE mesaje (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    id_conversatie INT NOT NULL,
                    id_expeditor INT NOT NULL,
                    mesaj TEXT NOT NULL,
                    citit TINYINT(1) DEFAULT 0,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_mesaje_conversatie (id_conversatie),
                    INDEX idx_mesaje_created_at (created_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        }
    } catch (PDOException $e) {
        error_log('Eroare creare tabele chat: ' . $e->getMessage());
        // Nu oprim execuția, continuăm cu tabelele existente
    }

    $initialized = true;
}

$method = $_SERVER['REQUEST_METHOD'];
$userId = verificaAutentificare();
ensureChatTables($db);

if ($method === 'GET') {
    $action = $_GET['action'] ?? 'conversatii';

    if ($action === 'conversatii') {
        try {
            // Query simplificat fără subquery complex
            $stmt = $db->prepare("
                SELECT 
                    c.*,
                    u1.nume AS initiator_nume,
                    u1.email AS initiator_email,
                    u1.telefon AS initiator_telefon,
                    u2.nume AS partener_nume,
                    u2.email AS partener_email,
                    u2.telefon AS partener_telefon
                FROM conversatii c
                JOIN utilizatori u1 ON c.id_utilizator_initiator = u1.id
                JOIN utilizatori u2 ON c.id_utilizator_partener = u2.id
                WHERE c.id_utilizator_initiator = ? OR c.id_utilizator_partener = ?
                ORDER BY c.updated_at DESC, c.created_at DESC
            ");
            $stmt->execute([$userId, $userId]);
            $conversatii = $stmt->fetchAll();
            
            // Adaugă ultimul mesaj pentru fiecare conversație
            foreach ($conversatii as &$conv) {
                $stmtMsg = $db->prepare("
                    SELECT id, mesaj, created_at, id_expeditor
                    FROM mesaje
                    WHERE id_conversatie = ?
                    ORDER BY created_at DESC
                    LIMIT 1
                ");
                $stmtMsg->execute([$conv['id']]);
                $ultimulMesaj = $stmtMsg->fetch();
                
                $conv['ultim_mesaj_id'] = $ultimulMesaj ? $ultimulMesaj['id'] : null;
                $conv['ultim_mesaj_text'] = $ultimulMesaj ? $ultimulMesaj['mesaj'] : null;
                $conv['ultim_mesaj_data'] = $ultimulMesaj ? $ultimulMesaj['created_at'] : null;
                $conv['ultim_mesaj_expeditor'] = $ultimulMesaj ? $ultimulMesaj['id_expeditor'] : null;
            }
            unset($conv);

            $formatted = array_map(function($row) use ($userId) {
                $esteInitiator = ($row['id_utilizator_initiator'] == $userId);
                $partener = [
                    'id' => $esteInitiator ? (int)$row['id_utilizator_partener'] : (int)$row['id_utilizator_initiator'],
                    'nume' => $esteInitiator ? $row['partener_nume'] : $row['initiator_nume'],
                    'email' => $esteInitiator ? $row['partener_email'] : $row['initiator_email'],
                    'telefon' => $esteInitiator ? $row['partener_telefon'] : $row['initiator_telefon']
                ];

                return [
                    'id' => (int)$row['id'],
                    'id_anunt' => $row['id_anunt'] ? (int)$row['id_anunt'] : null,
                    'initiator' => [
                        'id' => (int)$row['id_utilizator_initiator'],
                        'nume' => $row['initiator_nume'],
                        'email' => $row['initiator_email'],
                        'telefon' => $row['initiator_telefon']
                    ],
                    'partener' => $partener,
                    'subiect' => $row['subiect'],
                    'ultimul_mesaj' => $row['ultim_mesaj_text'],
                    'ultimul_mesaj_data' => $row['ultim_mesaj_data'],
                    'ultimul_mesaj_expeditor' => $row['ultim_mesaj_expeditor'] ? (int)$row['ultim_mesaj_expeditor'] : null,
                    'este_initiator' => $esteInitiator
                ];
            }, $conversatii);

            raspuns(['success' => true, 'conversatii' => $formatted]);
        } catch(PDOException $e) {
            error_log('Eroare conversatii: ' . $e->getMessage());
            eroare('Nu s-au putut încărca conversațiile: ' . $e->getMessage(), 500);
        }
    } elseif ($action === 'mesaje') {
        $conversatieId = intval($_GET['id'] ?? 0);
        if (!$conversatieId) {
            eroare('ID conversație invalid', 400);
        }

        try {
            $stmtCheck = $db->prepare("
                SELECT 1 FROM conversatii 
                WHERE id = ? AND (id_utilizator_initiator = ? OR id_utilizator_partener = ?)
            ");
            $stmtCheck->execute([$conversatieId, $userId, $userId]);
            if (!$stmtCheck->fetch()) {
                eroare('Nu ai acces la această conversație', 403);
            }

            $stmt = $db->prepare("
                SELECT m.*, u.nume AS nume_expeditor 
                FROM mesaje m
                JOIN utilizatori u ON u.id = m.id_expeditor
                WHERE m.id_conversatie = ?
                ORDER BY m.created_at ASC
            ");
            $stmt->execute([$conversatieId]);
            $mesaje = $stmt->fetchAll();

            $formatted = array_map(function($msg) {
                return [
                    'id' => (int)$msg['id'],
                    'id_conversatie' => (int)$msg['id_conversatie'],
                    'id_expeditor' => (int)$msg['id_expeditor'],
                    'nume_expeditor' => $msg['nume_expeditor'],
                    'mesaj' => $msg['mesaj'],
                    'citit' => (int)$msg['citit'],
                    'created_at' => $msg['created_at']
                ];
            }, $mesaje);

            raspuns(['success' => true, 'mesaje' => $formatted]);
        } catch(PDOException $e) {
            error_log('Eroare mesaje: ' . $e->getMessage());
            eroare('Nu s-au putut încărca mesajele', 500);
        }
    } else {
        eroare('Acțiune invalidă', 400);
    }
} elseif ($method === 'POST') {
    $payload = json_decode(file_get_contents('php://input'), true);
    if (!$payload) {
        eroare('Date invalide', 400);
    }

    $mesajText = trim($payload['mesaj'] ?? '');
    if ($mesajText === '') {
        eroare('Mesajul nu poate fi gol');
    }

    $conversatieId = isset($payload['id_conversatie']) ? intval($payload['id_conversatie']) : null;
    $destinatarId = isset($payload['id_destinatar']) ? intval($payload['id_destinatar']) : null;
    $anuntId = isset($payload['id_anunt']) ? intval($payload['id_anunt']) : null;

    if (!$conversatieId && !$destinatarId) {
        eroare('Trebuie să specifici conversația sau destinatarul', 400);
    }

    try {
        $db->beginTransaction();

        if ($conversatieId) {
            $stmtCheck = $db->prepare("
                SELECT id, id_utilizator_initiator, id_utilizator_partener 
                FROM conversatii 
                WHERE id = ? 
                LIMIT 1
            ");
            $stmtCheck->execute([$conversatieId]);
            $conversatie = $stmtCheck->fetch();

            if (!$conversatie || ($conversatie['id_utilizator_initiator'] != $userId && $conversatie['id_utilizator_partener'] != $userId)) {
                $db->rollBack();
                eroare('Nu ai acces la această conversație', 403);
            }
        } else {
            if ($destinatarId === $userId) {
                $db->rollBack();
                eroare('Nu îți poți trimite mesaje singur', 400);
            }

            $stmtExisting = $db->prepare("
                SELECT id FROM conversatii 
                WHERE (id_utilizator_initiator = :user AND id_utilizator_partener = :dest)
                   OR (id_utilizator_initiator = :dest AND id_utilizator_partener = :user)
                LIMIT 1
            ");
            $stmtExisting->execute(['user' => $userId, 'dest' => $destinatarId]);
            $existing = $stmtExisting->fetch();

            if ($existing) {
                $conversatieId = $existing['id'];
            } else {
                $stmtConv = $db->prepare("
                    INSERT INTO conversatii (id_anunt, id_utilizator_initiator, id_utilizator_partener, subiect, ultimul_mesaj)
                    VALUES (?, ?, ?, ?, NOW())
                ");
                $subiect = $payload['subiect'] ?? null;
                $stmtConv->execute([
                    $anuntId ?: null,
                    $userId,
                    $destinatarId,
                    $subiect
                ]);
                $conversatieId = $db->lastInsertId();
            }
        }

        $stmtMsg = $db->prepare("
            INSERT INTO mesaje (id_conversatie, id_expeditor, mesaj)
            VALUES (?, ?, ?)
        ");
        $stmtMsg->execute([$conversatieId, $userId, $mesajText]);

        $stmtUpdate = $db->prepare("
            UPDATE conversatii 
            SET ultimul_mesaj = NOW(), updated_at = NOW()
            WHERE id = ?
        ");
        $stmtUpdate->execute([$conversatieId]);

        $db->commit();

        raspuns([
            'success' => true,
            'mesaj' => 'Mesaj trimis',
            'conversatie_id' => (int)$conversatieId
        ], 201);
    } catch(PDOException $e) {
        $db->rollBack();
        error_log('Eroare trimitere mesaj: ' . $e->getMessage());
        eroare('Nu am putut trimite mesajul', 500);
    }
} elseif ($method === 'PUT') {
    $payload = json_decode(file_get_contents('php://input'), true);
    if (!$payload || !isset($payload['id_conversatie'])) {
        eroare('Date invalide', 400);
    }

    $conversatieId = intval($payload['id_conversatie']);

    try {
        $stmtCheck = $db->prepare("
            SELECT 1 FROM conversatii 
            WHERE id = ? AND (id_utilizator_initiator = ? OR id_utilizator_partener = ?)
        ");
        $stmtCheck->execute([$conversatieId, $userId, $userId]);
        if (!$stmtCheck->fetch()) {
            eroare('Nu ai acces la această conversație', 403);
        }

        $stmt = $db->prepare("
            UPDATE mesaje 
            SET citit = 1 
            WHERE id_conversatie = ? AND id_expeditor != ?
        ");
        $stmt->execute([$conversatieId, $userId]);

        raspuns(['success' => true, 'mesaj' => 'Mesaje marcate ca citite']);
    } catch(PDOException $e) {
        error_log('Eroare marcarea citit: ' . $e->getMessage());
        eroare('Nu am putut marca mesajele', 500);
    }
} else {
    eroare('Metodă HTTP neacceptată', 405);
}
