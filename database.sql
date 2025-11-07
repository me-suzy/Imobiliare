-- Bază de date pentru site anunțuri
-- Rulează în phpMyAdmin sau MySQL Workbench

CREATE DATABASE IF NOT EXISTS anunturi_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE anunturi_db;

-- Tabel utilizatori
CREATE TABLE IF NOT EXISTS utilizatori (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nume VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    parola VARCHAR(255) NOT NULL,
    telefon VARCHAR(20),
    avatar VARCHAR(255),
    tip_cont ENUM('user', 'admin') DEFAULT 'user',
    sold_cont DECIMAL(10, 2) DEFAULT 0.00,
    credite_disponibile INT DEFAULT 0,
    data_creare DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_actualizare DATETIME ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_tip_cont (tip_cont)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel anunțuri
CREATE TABLE IF NOT EXISTS anunturi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_utilizator INT NOT NULL,
    titlu VARCHAR(255) NOT NULL,
    descriere TEXT NOT NULL,
    categorie VARCHAR(50) NOT NULL,
    pret DECIMAL(10, 2) DEFAULT 0,
    moneda VARCHAR(10) DEFAULT 'RON',
    oras VARCHAR(100),
    judet VARCHAR(100),
    imagini JSON,
    status ENUM('activ', 'inactiv', 'sters', 'vandut') DEFAULT 'activ',
    vizualizari INT DEFAULT 0,
    data_creare DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_actualizare DATETIME ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_utilizator) REFERENCES utilizatori(id) ON DELETE CASCADE,
    INDEX idx_categorie (categorie),
    INDEX idx_oras (oras),
    INDEX idx_status (status),
    INDEX idx_data_creare (data_creare DESC),
    FULLTEXT idx_cautare (titlu, descriere)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel mesaje (între utilizatori)
CREATE TABLE IF NOT EXISTS mesaje (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_expeditor INT NOT NULL,
    id_destinatar INT NOT NULL,
    id_anunt INT,
    continut TEXT NOT NULL,
    citit BOOLEAN DEFAULT FALSE,
    data_trimitere DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_expeditor) REFERENCES utilizatori(id) ON DELETE CASCADE,
    FOREIGN KEY (id_destinatar) REFERENCES utilizatori(id) ON DELETE CASCADE,
    FOREIGN KEY (id_anunt) REFERENCES anunturi(id) ON DELETE SET NULL,
    INDEX idx_destinatar (id_destinatar),
    INDEX idx_citit (citit)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel favorite (anunțuri salvate de utilizatori)
CREATE TABLE IF NOT EXISTS favorite (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_utilizator INT NOT NULL,
    id_anunt INT NOT NULL,
    data_adaugare DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_utilizator) REFERENCES utilizatori(id) ON DELETE CASCADE,
    FOREIGN KEY (id_anunt) REFERENCES anunturi(id) ON DELETE CASCADE,
    UNIQUE KEY unique_favorit (id_utilizator, id_anunt),
    INDEX idx_utilizator (id_utilizator)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel notificări
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

-- Tabel plăți
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

-- Tabel ratinguri
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

-- Tabel pachete promoționale
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

-- Tabel sesiuni logare (pentru tracking admin)
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

-- Date de test (opțional)
INSERT INTO utilizatori (nume, email, parola, telefon) VALUES
('Test User', 'test@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0722123456');
-- Parola: "password"

-- Indice pentru căutare full-text (dacă MySQL >= 5.6)
-- ALTER TABLE anunturi ADD FULLTEXT INDEX idx_cautare (titlu, descriere);

