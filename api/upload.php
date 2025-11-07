<?php
require_once 'config.php';

// Verifică autentificare
$userId = verificaAutentificare();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    eroare('Metodă invalidă');
}

if (!isset($_FILES['imagini'])) {
    eroare('Nicio imagine încărcată');
}

$db = getDB();
if (!$db) {
    eroare('Eroare de conexiune la baza de date');
}

// Obține numele utilizatorului pentru denumirea imaginilor
$stmt = $db->prepare("SELECT nume FROM utilizatori WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    eroare('Utilizator negăsit');
}

// Curăță numele utilizatorului pentru a fi folosit în numele fișierului
$numeUtilizator = preg_replace('/[^a-zA-Z0-9_]/', '_', strtolower($user['nume']));
$numeUtilizator = substr($numeUtilizator, 0, 50); // Limitează la 50 de caractere

// Creează folder-ul pentru imagini clienti
$uploadDir = '../imagini_clienti/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$imaginiUrl = [];
$erori = [];

// Procesează fiecare imagine (maxim 6)
$files = $_FILES['imagini'];
$fileCount = min(count($files['name']), 6); // Limitează la 6 imagini

for ($i = 0; $i < $fileCount; $i++) {
    
    $fileName = $files['name'][$i];
    $fileTmpName = $files['tmp_name'][$i];
    $fileSize = $files['size'][$i];
    $fileError = $files['error'][$i];
    
    if ($fileError !== UPLOAD_ERR_OK) {
        $erori[] = "Eroare la upload: $fileName";
        continue;
    }
    
    // Verifică tipul fișierului
    $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    if (!in_array($fileType, $allowedTypes)) {
        $erori[] = "Tip fișier invalid: $fileName (doar JPG, PNG, GIF, WEBP)";
        continue;
    }
    
    // Verifică dimensiunea (max 5MB)
    if ($fileSize > 5 * 1024 * 1024) {
        $erori[] = "Fișier prea mare: $fileName (max 5MB)";
        continue;
    }
    
    // Generează nume: nume_client_1.jpg, nume_client_2.jpg, etc.
    $indexImagine = $i + 1;
    $numeFisier = $numeUtilizator . '_' . $indexImagine . '.' . $fileType;
    
    // Dacă fișierul există deja, adaugă timestamp pentru unicitate
    $destinatie = $uploadDir . $numeFisier;
    if (file_exists($destinatie)) {
        $timestamp = time();
        $numeFisier = $numeUtilizator . '_' . $indexImagine . '_' . $timestamp . '.' . $fileType;
        $destinatie = $uploadDir . $numeFisier;
    }
    
    // Mută fișierul
    if (move_uploaded_file($fileTmpName, $destinatie)) {
        // Returnează URL-ul relativ
        $imaginiUrl[] = 'imagini_clienti/' . $numeFisier;
    } else {
        $erori[] = "Eroare la salvare: $fileName";
    }
}

if (empty($imaginiUrl) && !empty($erori)) {
    eroare('Erori la upload: ' . implode(', ', $erori));
}

raspuns([
    'success' => true,
    'mesaj' => 'Imagini încărcate cu succes!',
    'imagini' => $imaginiUrl,
    'erori' => $erori
]);
?>

