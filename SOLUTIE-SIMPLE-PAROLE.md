# 🎯 Soluție Simplă - Parole Admin

## Problema phpMyAdmin

Dacă phpMyAdmin este blocat, folosește **MySQL Command Line** sau **scripturile PHP**.

## ✅ Soluție Rapidă - Fără phpMyAdmin

### Pasul 1: Rulează Scriptul de Sincronizare

```
http://localhost/sincronizeaza-parole.php
```

Acest script:
- ✅ Creează tabelul `parole_admin` automat
- ✅ Sincronizează toate parolele
- ✅ Nu necesită phpMyAdmin

### Pasul 2: Login ca Admin

```
http://localhost/login.html
```

- Email: `admin@marc.ro`
- Parolă: `password`

### Pasul 3: Accesează Parolele

```
http://localhost/admin-parole.html
```

## 🔧 Dacă Scriptul de Sincronizare Nu Funcționează

### Opțiunea A: MySQL Command Line

1. Deschide Command Prompt
2. Rulează:
```bash
cd C:\xampp\mysql\bin
mysql.exe -u root
```

3. În MySQL, rulează:
```sql
USE anunturi_db;

-- Creează tabelul
CREATE TABLE IF NOT EXISTS parole_admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_utilizator INT NOT NULL UNIQUE,
    parola_criptata TEXT NOT NULL,
    data_creare DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_utilizator) REFERENCES utilizatori(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Sincronizează parolele (folosește scriptul PHP pentru asta)
```

### Opțiunea B: Simplifică Structura

Dacă foreign keys cauzează probleme, creează tabelul fără foreign key:

```sql
USE anunturi_db;

CREATE TABLE IF NOT EXISTS parole_admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_utilizator INT NOT NULL UNIQUE,
    parola_criptata TEXT NOT NULL,
    data_creare DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
```

## 🎯 Rezumat

**Cel mai simplu:**
1. Rulează `sincronizeaza-parole.php` - creează totul automat
2. Login ca admin
3. Accesează `admin-parole.html`

**Dacă nu funcționează:**
- Repornește XAMPP (Apache + MySQL)
- Rulează `fix-phpmyadmin.php`
- Încearcă din nou

---

**Nu ai nevoie de phpMyAdmin pentru a folosi sistemul!** Scripturile PHP fac totul automat! 🚀

