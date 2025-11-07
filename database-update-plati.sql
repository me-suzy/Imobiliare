-- Actualizare și popolare tabel plati
-- Rulează în phpMyAdmin: http://localhost/phpmyadmin/

USE anunturi_db;

-- Asigură-te că tabelul plati există (dacă nu, va fi creat din database.sql)
-- Verifică dacă există și adaugă date de test

-- 1. Verifică dacă există utilizatori și anunțuri
-- Dacă nu există, creează-i mai întâi

-- 2. Adaugă plăți de test (doar dacă nu există deja)
INSERT INTO plati (id_utilizator, id_anunt, tip, valoare, moneda, status, id_tranzactie, data_creare) 
SELECT 
    1 as id_utilizator,
    NULL as id_anunt,
    'credit' as tip,
    100.00 as valoare,
    'RON' as moneda,
    'completed' as status,
    CONCAT('TXN-', UNIX_TIMESTAMP(), '-001') as id_tranzactie,
    NOW() as data_creare
WHERE NOT EXISTS (SELECT 1 FROM plati WHERE id_tranzactie LIKE 'TXN-%');

INSERT INTO plati (id_utilizator, id_anunt, tip, valoare, moneda, status, id_tranzactie, data_creare) 
SELECT 
    (SELECT MIN(id) FROM utilizatori WHERE tip_cont = 'admin') as id_utilizator,
    (SELECT MIN(id) FROM anunturi LIMIT 1) as id_anunt,
    'promovare' as tip,
    50.00 as valoare,
    'RON' as moneda,
    'completed' as status,
    CONCAT('TXN-', UNIX_TIMESTAMP(), '-002') as id_tranzactie,
    DATE_SUB(NOW(), INTERVAL 2 DAY) as data_creare
WHERE EXISTS (SELECT 1 FROM utilizatori) 
  AND EXISTS (SELECT 1 FROM anunturi)
  AND NOT EXISTS (SELECT 1 FROM plati WHERE id_tranzactie LIKE 'TXN-%002');

INSERT INTO plati (id_utilizator, id_anunt, tip, valoare, moneda, status, id_tranzactie, data_creare) 
SELECT 
    (SELECT MIN(id) FROM utilizatori) as id_utilizator,
    NULL as id_anunt,
    'pachet' as tip,
    200.00 as valoare,
    'RON' as moneda,
    'completed' as status,
    CONCAT('TXN-', UNIX_TIMESTAMP(), '-003') as id_tranzactie,
    DATE_SUB(NOW(), INTERVAL 5 DAY) as data_creare
WHERE EXISTS (SELECT 1 FROM utilizatori)
  AND NOT EXISTS (SELECT 1 FROM plati WHERE id_tranzactie LIKE 'TXN-%003');

INSERT INTO plati (id_utilizator, id_anunt, tip, valoare, moneda, status, id_tranzactie, data_creare) 
SELECT 
    (SELECT MAX(id) FROM utilizatori) as id_utilizator,
    (SELECT MAX(id) FROM anunturi LIMIT 1) as id_anunt,
    'promovare' as tip,
    75.00 as valoare,
    'RON' as moneda,
    'pending' as status,
    CONCAT('TXN-', UNIX_TIMESTAMP(), '-004') as id_tranzactie,
    DATE_SUB(NOW(), INTERVAL 1 HOUR) as data_creare
WHERE EXISTS (SELECT 1 FROM utilizatori)
  AND EXISTS (SELECT 1 FROM anunturi)
  AND NOT EXISTS (SELECT 1 FROM plati WHERE id_tranzactie LIKE 'TXN-%004');

-- Alternative simplificată (dacă query-urile de mai sus nu funcționează):
-- INSERT INTO plati (id_utilizator, id_anunt, tip, valoare, moneda, status, id_tranzactie, data_creare) VALUES
-- (1, NULL, 'credit', 100.00, 'RON', 'completed', CONCAT('TXN-', UNIX_TIMESTAMP(), '-001'), NOW()),
-- (2, 1, 'promovare', 50.00, 'RON', 'completed', CONCAT('TXN-', UNIX_TIMESTAMP(), '-002'), DATE_SUB(NOW(), INTERVAL 2 DAY)),
-- (3, NULL, 'pachet', 200.00, 'RON', 'completed', CONCAT('TXN-', UNIX_TIMESTAMP(), '-003'), DATE_SUB(NOW(), INTERVAL 5 DAY)),
-- (1, 2, 'promovare', 75.00, 'RON', 'pending', CONCAT('TXN-', UNIX_TIMESTAMP(), '-004'), DATE_SUB(NOW(), INTERVAL 1 HOUR));

