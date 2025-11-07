-- Tabel pentru stocarea parolelor în format criptat (pentru admin)
-- ⚠️ ATENȚIE: Acest tabel este opțional și doar pentru dezvoltare/admin
-- NU folosi acest tabel în producție pentru securitate maximă!

USE anunturi_db;

-- Tabel pentru parole criptate (vizibile doar adminului)
CREATE TABLE IF NOT EXISTS parole_admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_utilizator INT NOT NULL UNIQUE,
    parola_criptata TEXT NOT NULL,
    data_creare DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_actualizare DATETIME ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_utilizator) REFERENCES utilizatori(id) ON DELETE CASCADE,
    INDEX idx_utilizator (id_utilizator)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Funcție helper pentru criptare/decriptare (va fi folosită în PHP)
-- Cheia de criptare - SCHIMBĂ-O pentru securitate!
-- Poți pune această cheie în config.php

