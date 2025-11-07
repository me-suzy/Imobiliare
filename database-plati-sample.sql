-- Date de test pentru tabelul plati
-- Rulează în phpMyAdmin pentru a adăuga plăți de test

USE anunturi_db;

-- Inserează plăți de test (dacă nu există deja)
INSERT INTO plati (id_utilizator, id_anunt, tip, valoare, moneda, status, id_tranzactie, data_creare) VALUES
(1, NULL, 'credit', 100.00, 'RON', 'completed', 'TXN-' || UNIX_TIMESTAMP() || '-001', NOW()),
(2, 1, 'promovare', 50.00, 'RON', 'completed', 'TXN-' || UNIX_TIMESTAMP() || '-002', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(3, NULL, 'pachet', 200.00, 'RON', 'completed', 'TXN-' || UNIX_TIMESTAMP() || '-003', DATE_SUB(NOW(), INTERVAL 5 DAY)),
(1, 2, 'promovare', 75.00, 'RON', 'pending', 'TXN-' || UNIX_TIMESTAMP() || '-004', DATE_SUB(NOW(), INTERVAL 1 HOUR)),
(2, NULL, 'credit', 150.00, 'RON', 'completed', 'TXN-' || UNIX_TIMESTAMP() || '-005', DATE_SUB(NOW(), INTERVAL 3 DAY));

-- Alternativ, pentru MySQL fără funcții:
-- INSERT INTO plati (id_utilizator, id_anunt, tip, valoare, moneda, status, id_tranzactie, data_creare) VALUES
-- (1, NULL, 'credit', 100.00, 'RON', 'completed', CONCAT('TXN-', UNIX_TIMESTAMP(), '-001'), NOW()),
-- (2, 1, 'promovare', 50.00, 'RON', 'completed', CONCAT('TXN-', UNIX_TIMESTAMP(), '-002'), DATE_SUB(NOW(), INTERVAL 2 DAY)),
-- (3, NULL, 'pachet', 200.00, 'RON', 'completed', CONCAT('TXN-', UNIX_TIMESTAMP(), '-003'), DATE_SUB(NOW(), INTERVAL 5 DAY));

