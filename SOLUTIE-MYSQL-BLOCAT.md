# 🔧 Soluție MySQL Blocat

## ❌ Problema

- `admin-parole.html` se încarcă la infinit
- `phpMyAdmin` se blochează
- Baza de date nu răspunde

## 🔍 Cauze Posibile

1. **Foreign keys blocate** - Foreign keys pot cauza deadlock-uri
2. **Interogări lente** - Interogări complexe pot bloca MySQL
3. **MySQL blocat** - Serviciul MySQL nu răspunde
4. **Tabele corupte** - Structura tabelelor poate fi coruptă

## ✅ Soluții

### Soluția 1: Repornește MySQL (RAPID)

1. Deschide **XAMPP Control Panel**
2. **Oprește MySQL** (Stop)
3. **Așteaptă 10-15 secunde**
4. **Pornește MySQL** din nou (Start)
5. Încearcă din nou

### Soluția 2: Testează MySQL

Rulează:
```
http://localhost/test-mysql.php
```

Acest script verifică:
- ✅ Dacă MySQL răspunde
- ✅ Dacă conexiunea funcționează
- ✅ Dacă există procese blocate
- ✅ Timpul de răspuns

### Soluția 3: Repară Blocări

Rulează:
```
http://localhost/fix-mysql-blocks.php
```

Acest script:
- ✅ Elimină foreign keys problematice
- ✅ Verifică structura tabelelor
- ✅ Repară blocări

### Soluția 4: Simplifică Structura

Dacă problema persistă, elimină foreign keys manual:

1. Deschide **Command Prompt**
2. Rulează:
```bash
cd C:\xampp\mysql\bin
mysql.exe -u root
```

3. În MySQL:
```sql
USE anunturi_db;
SET FOREIGN_KEY_CHECKS = 0;

-- Elimină foreign keys din parole_admin
ALTER TABLE parole_admin DROP FOREIGN KEY parole_admin_ibfk_1;

SET FOREIGN_KEY_CHECKS = 1;
```

### Soluția 5: Recreatează Tabelele

Dacă nimic nu funcționează:

1. **Backup date** (dacă ai date importante)
2. **Șterge tabelele problematice:**
```sql
DROP TABLE IF EXISTS parole_admin;
DROP TABLE IF EXISTS sesiuni_logare;
```

3. **Recreatează-le fără foreign keys:**
```sql
CREATE TABLE parole_admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_utilizator INT NOT NULL UNIQUE,
    parola_criptata TEXT NOT NULL,
    data_creare DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_utilizator (id_utilizator)
) ENGINE=InnoDB;
```

## 🚀 Quick Fix

**Cel mai rapid:**

1. **Repornește MySQL în XAMPP**
2. **Rulează `test-mysql.php`** - verifică dacă funcționează
3. **Rulează `fix-mysql-blocks.php`** - repară blocări
4. **Încearcă din nou `admin-parole.html`**

## 📝 Notă

Problema este cel mai probabil cauzată de:
- **Foreign keys** care blochează interogările
- **Interogări complexe** care durează prea mult
- **MySQL blocat** care nu răspunde

**Soluția recomandată:** Elimină foreign keys din tabelele noi create (`parole_admin`, `sesiuni_logare`).

---

**Dacă problema persistă după repornire, rulează `fix-mysql-blocks.php`!** 🚀

