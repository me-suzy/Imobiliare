-- Structură necesară pentru funcționalitatea de mesagerie
-- Rulează aceste comenzi în phpMyAdmin (tab „SQL”) sau dintr-un client MySQL conectat la baza folosită de proiect.
-- Înlocuiește `anunturi_db` cu numele bazei tale, dacă este diferit.

USE anunturi_db;

-- Conversații între doi utilizatori (un anunț opțional)
CREATE TABLE IF NOT EXISTS conversatii (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_anunt INT NULL,
    id_utilizator_initiator INT NOT NULL,
    id_utilizator_partener INT NOT NULL,
    subiect VARCHAR(255) NULL,
    ultimul_mesaj DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_conversatie (id_anunt, id_utilizator_initiator, id_utilizator_partener),
    CONSTRAINT fk_conversatii_anunt FOREIGN KEY (id_anunt) REFERENCES anunturi(id) ON DELETE SET NULL,
    CONSTRAINT fk_conversatii_initiator FOREIGN KEY (id_utilizator_initiator) REFERENCES utilizatori(id) ON DELETE CASCADE,
    CONSTRAINT fk_conversatii_partener FOREIGN KEY (id_utilizator_partener) REFERENCES utilizatori(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Mesaje trimise într-o conversație
CREATE TABLE IF NOT EXISTS mesaje (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_conversatie INT NOT NULL,
    id_expeditor INT NOT NULL,
    mesaj TEXT NOT NULL,
    citit TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_mesaje_conversatie FOREIGN KEY (id_conversatie) REFERENCES conversatii(id) ON DELETE CASCADE,
    CONSTRAINT fk_mesaje_expeditor FOREIGN KEY (id_expeditor) REFERENCES utilizatori(id) ON DELETE CASCADE,
    INDEX idx_mesaje_conversatie (id_conversatie),
    INDEX idx_mesaje_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Index pentru căutare rapidă a conversațiilor unui utilizator
CREATE INDEX IF NOT EXISTS idx_conversatii_initiator ON conversatii(id_utilizator_initiator);
CREATE INDEX IF NOT EXISTS idx_conversatii_partener ON conversatii(id_utilizator_partener);


