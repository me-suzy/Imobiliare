# 📋 Ghid Instalare Tabel Sesiuni Logare

## Opțiunea 1: Script Automat (RECOMANDAT) ✅

1. **Deschide browser-ul** și accesează:
   ```
   http://localhost/install-sesiuni-logare.php
   ```

2. **Script-ul va:**
   - Se conectează automat la baza de date
   - Selectează baza de date `anunturi_db`
   - Creează tabelul `sesiuni_logare`
   - Afișează structura și datele existente

3. **Gata!** ✅

---

## Opțiunea 2: phpMyAdmin Manual

### Pasul 1: Deschide phpMyAdmin
```
http://localhost/phpmyadmin/index.php
```

### Pasul 2: Selectează Baza de Date
1. În stânga, click pe **`anunturi_db`** (sau creează-o dacă nu există)
2. **IMPORTANT:** Trebuie să fie selectată baza de date înainte de a rula comanda!

### Pasul 3: Rulează Comanda SQL
1. Click pe tab-ul **"SQL"** (sus în meniu)
2. Copiază și lipește următoarea comandă:

```sql
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
```

3. Click pe butonul **"Go"** (sau apasă **Ctrl+Enter**)

### Pasul 4: Verifică
Dacă totul e OK, vei vedea mesajul:
```
Table 'sesiuni_logare' has been created.
```

---

## Opțiunea 3: MySQL Command Line

Dacă preferi linia de comandă:

```bash
mysql -u root -p
```

Apoi:
```sql
USE anunturi_db;

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
```

---

## Verificare

După instalare, verifică că tabelul există:

```sql
SHOW TABLES LIKE 'sesiuni_logare';
DESCRIBE sesiuni_logare;
```

---

## Utilizare

Odată instalat, tabelul va fi populat automat:
- La fiecare **login** → se creează o înregistrare nouă
- La fiecare **logout** → se marchează sesiunea ca închisă
- În **Admin Panel** → tab "Sesiuni Logare" → vezi toate sesiunile

---

**💡 RECOMANDARE:** Folosește **Opțiunea 1** (script automat) - e cea mai simplă! 🚀

