-- Bază de date pentru platforma de anunțuri
-- Rulează acest script în phpMyAdmin sau MySQL Workbench

CREATE DATABASE IF NOT EXISTS anunturi_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE anunturi_db;

-- Tabelă utilizatori
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    location_county VARCHAR(100),
    location_city VARCHAR(100),
    avatar VARCHAR(255),
    rating_average DECIMAL(2,1) DEFAULT 0,
    rating_count INT DEFAULT 0,
    verified BOOLEAN DEFAULT FALSE,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabelă anunțuri
CREATE TABLE ads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    category ENUM('imobiliare', 'auto-moto', 'mobilier-casa', 'joburi', 'electronice', 'animale', 'fashion', 'sport', 'altele') NOT NULL,
    subcategory VARCHAR(100),
    price DECIMAL(12,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'RON',
    negotiable BOOLEAN DEFAULT FALSE,
    condition_type ENUM('nou', 'folosit') NOT NULL,
    location_county VARCHAR(100) NOT NULL,
    location_city VARCHAR(100) NOT NULL,
    location_address VARCHAR(255),
    contact_name VARCHAR(100) NOT NULL,
    contact_phone VARCHAR(20) NOT NULL,
    contact_email VARCHAR(255),
    status ENUM('active', 'pending', 'sold', 'expired', 'deleted') DEFAULT 'active',
    is_promoted BOOLEAN DEFAULT FALSE,
    promoted_until DATETIME,
    views INT DEFAULT 0,
    favorites INT DEFAULT 0,
    expires_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_category (category),
    INDEX idx_status (status),
    INDEX idx_user (user_id),
    INDEX idx_created (created_at),
    INDEX idx_price (price),
    FULLTEXT idx_search (title, description)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabelă imagini anunțuri
CREATE TABLE ad_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ad_id INT NOT NULL,
    filename VARCHAR(255) NOT NULL,
    url VARCHAR(255) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ad_id) REFERENCES ads(id) ON DELETE CASCADE,
    INDEX idx_ad (ad_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabelă conversații
CREATE TABLE conversations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ad_id INT NOT NULL,
    participant1_id INT NOT NULL,
    participant2_id INT NOT NULL,
    last_message_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ad_id) REFERENCES ads(id) ON DELETE CASCADE,
    FOREIGN KEY (participant1_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (participant2_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_participants (participant1_id, participant2_id),
    INDEX idx_ad (ad_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabelă mesaje
CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    conversation_id INT NOT NULL,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    content TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_conversation (conversation_id),
    INDEX idx_sender (sender_id),
    INDEX idx_receiver (receiver_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabelă favorite
CREATE TABLE favorites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    ad_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (ad_id) REFERENCES ads(id) ON DELETE CASCADE,
    UNIQUE KEY unique_favorite (user_id, ad_id),
    INDEX idx_user (user_id),
    INDEX idx_ad (ad_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Trigger pentru expirarea automată a anunțurilor
DELIMITER $$
CREATE TRIGGER set_ad_expiry BEFORE INSERT ON ads
FOR EACH ROW
BEGIN
    IF NEW.expires_at IS NULL THEN
        SET NEW.expires_at = DATE_ADD(NOW(), INTERVAL 30 DAY);
    END IF;
END$$
DELIMITER ;

-- Date de test (opțional)
INSERT INTO users (name, email, password, phone, location_county, location_city) VALUES
('Ion Popescu', 'ion@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0722123456', 'bucuresti', 'București'),
('Maria Ionescu', 'maria@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0733987654', 'cluj', 'Cluj-Napoca');

-- Parola pentru testare este: "password"


