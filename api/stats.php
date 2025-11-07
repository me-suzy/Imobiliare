<?php
require_once 'config.php';

$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];

// ========== STATISTICI GLOBALE ==========
if ($method === 'GET') {
    
    try {
        // Total anunțuri active
        $stmt = $db->query("SELECT COUNT(*) as total FROM anunturi WHERE status = 'activ'");
        $totalAnunturi = $stmt->fetch()['total'];
        
        // Total utilizatori
        $stmt = $db->query("SELECT COUNT(*) as total FROM utilizatori");
        $totalUtilizatori = $stmt->fetch()['total'];
        
        // Anunțuri noi azi
        $stmt = $db->query("SELECT COUNT(*) as total FROM anunturi WHERE DATE(data_creare) = CURDATE() AND status = 'activ'");
        $anunturiNoiAzi = $stmt->fetch()['total'];
        
        // Anunțuri noi în ultimele 7 zile
        $stmt = $db->query("SELECT COUNT(*) as total FROM anunturi WHERE data_creare >= DATE_SUB(NOW(), INTERVAL 7 DAY) AND status = 'activ'");
        $anunturiNoiSaptamana = $stmt->fetch()['total'];
        
        raspuns([
            'anunturi' => (int)$totalAnunturi,
            'utilizatori' => (int)$totalUtilizatori,
            'anunturi_noi_azi' => (int)$anunturiNoiAzi,
            'anunturi_noi_saptamana' => (int)$anunturiNoiSaptamana
        ]);
        
    } catch(PDOException $e) {
        eroare('Eroare la preluarea statisticilor: ' . $e->getMessage(), 500);
    }
}

else {
    eroare('Metodă HTTP invalidă');
}
?>

