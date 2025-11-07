-- Script pentru actualizarea bazei de date existente
-- Rulează în phpMyAdmin pentru a adăuga noile tabele și coloane

USE anunturi_db;

-- Adaugă coloane noi în tabelul utilizatori (dacă nu există)
ALTER TABLE utilizatori 
ADD COLUMN IF NOT EXISTS tip_cont ENUM('user', 'admin') DEFAULT 'user' AFTER avatar,
ADD COLUMN IF NOT EXISTS sold_cont DECIMAL(10, 2) DEFAULT 0.00 AFTER tip_cont,
ADD COLUMN IF NOT EXISTS credite_disponibile INT DEFAULT 0 AFTER sold_cont;

-- Adaugă index pentru tip_cont
ALTER TABLE utilizatori ADD INDEX IF NOT EXISTS idx_tip_cont (tip_cont);

-- Creează tabelul notificări (dacă nu există)
CREATE TABLE IF NOT EXISTS notificari (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_utilizator INT NOT NULL,
    tip VARCHAR(50) NOT NULL,
    titlu VARCHAR(255) NOT NULL,
    mesaj TEXT NOT NULL,
    link VARCHAR(255),
    citita BOOLEAN DEFAULT FALSE,
    data_creare DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_utilizator) REFERENCES utilizatori(id) ON DELETE CASCADE,
    INDEX idx_utilizator (id_utilizator),
    INDEX idx_citita (citita),
    INDEX idx_data_creare (data_creare DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Creează tabelul plăți (dacă nu există)
CREATE TABLE IF NOT EXISTS plati (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_utilizator INT NOT NULL,
    id_anunt INT,
    tip ENUM('promovare', 'pachet', 'credit') NOT NULL,
    valoare DECIMAL(10, 2) NOT NULL,
    moneda VARCHAR(10) DEFAULT 'EUR',
    status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    id_tranzactie VARCHAR(100),
    data_creare DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_actualizare DATETIME ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_utilizator) REFERENCES utilizatori(id) ON DELETE CASCADE,
    FOREIGN KEY (id_anunt) REFERENCES anunturi(id) ON DELETE SET NULL,
    INDEX idx_utilizator (id_utilizator),
    INDEX idx_status (status),
    INDEX idx_data_creare (data_creare DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Creează tabelul ratinguri (dacă nu există)
CREATE TABLE IF NOT EXISTS ratinguri (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_utilizator_vanzator INT NOT NULL,
    id_utilizator_cumparator INT NOT NULL,
    id_anunt INT,
    stele INT NOT NULL CHECK (stele >= 1 AND stele <= 5),
    comentariu TEXT,
    data_creare DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_utilizator_vanzator) REFERENCES utilizatori(id) ON DELETE CASCADE,
    FOREIGN KEY (id_utilizator_cumparator) REFERENCES utilizatori(id) ON DELETE CASCADE,
    FOREIGN KEY (id_anunt) REFERENCES anunturi(id) ON DELETE SET NULL,
    INDEX idx_vanzator (id_utilizator_vanzator),
    INDEX idx_cumparator (id_utilizator_cumparator)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Creează tabelul pachete promoționale (dacă nu există)
CREATE TABLE IF NOT EXISTS pachete_promotii (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nume VARCHAR(100) NOT NULL,
    categorie VARCHAR(50),
    pret DECIMAL(10, 2) NOT NULL,
    credite_incluse INT DEFAULT 0,
    zile_valabilitate INT DEFAULT 30,
    descriere TEXT,
    activ BOOLEAN DEFAULT TRUE,
    data_creare DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Creează contul admin (parola: admin123)
INSERT INTO utilizatori (nume, email, parola, telefon, tip_cont, sold_cont, credite_disponibile) 
VALUES ('Administrator', 'admin@marc.ro', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0722000000', 'admin', 0.00, 1000)
ON DUPLICATE KEY UPDATE tip_cont='admin';

-- Inserează date pentru utilizatorul de test (dacă nu există)
INSERT INTO utilizatori (nume, email, parola, telefon, tip_cont) 
VALUES ('Ionel Bălăuță', 'ionel@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0722123456', 'user')
ON DUPLICATE KEY UPDATE nume='Ionel Bălăuță';

-- Actualizează utilizatorul existent "Test User" cu numele corect
UPDATE utilizatori SET nume = 'Ionel Bălăuță' WHERE email = 'test@example.com' AND nume = 'Test User';

-- Creează tabelul sesiuni logare (dacă nu există)
CREATE TABLE IF NOT EXISTS sesiuni_logare (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_utilizator INT NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    metoda_autentificare ENUM('email', 'facebook', 'google', 'apple') DEFAULT 'email',
    data_logare DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_logout DATETIME,
    activ BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (id_utilizator) REFERENCES utilizatori(id) ON DELETE CASCADE,
    INDEX idx_utilizator (id_utilizator),
    INDEX idx_data_logare (data_logare DESC),
    INDEX idx_activ (activ)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserează pachete promoționale de bază
INSERT INTO pachete_promotii (nume, categorie, pret, credite_incluse, zile_valabilitate, descriere) VALUES
('Promovare Standard', NULL, 22.29, 0, 7, 'Promovare anunț pentru 7 zile'),
('Promovare Premium', NULL, 45.99, 0, 14, 'Promovare anunț pentru 14 zile'),
('Pachet Basic', NULL, 49.99, 5, 30, '5 credite pentru promovări'),
('Pachet Pro', NULL, 99.99, 12, 30, '12 credite pentru promovări'),
('Pachet Business', NULL, 199.99, 30, 30, '30 credite pentru promovări')
ON DUPLICATE KEY UPDATE activ=TRUE;

