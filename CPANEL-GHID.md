# 🌐 Ghid cPanel - Creare Bază de Date pentru marc.ro

## 📋 **PREZENTARE GENERALĂ**

Vei crea baza de date pe serverul tău de hosting, apoi vei importa structura din `database.sql`.

**Timp estimat: 5 minute**

---

## 🎯 **PASUL 1: Acces la cPanel (1 minut)**

### **1.1. Deschide cPanel:**

**URL-ul de acces (variante comune):**
```
https://marc.ro:2083
SAU
https://marc.ro/cpanel
SAU
https://cpanel.marc.ro
SAU
https://server-tau.hosting-provider.com:2083
```

**⚠️ URL-ul exact îl primești de la furnizorul de hosting!**

---

### **1.2. Login:**

**Username:** (primit de la hosting)  
**Password:** (primit de la hosting)

---

## 🎯 **PASUL 2: Creează Baza de Date (2 minute)**

### **2.1. Găsește secțiunea "Databases":**

**În cPanel Dashboard, caută:**
```
📊 Databases
   └─ MySQL® Databases  ← CLICK AICI!
```

**SAU caută în bara de căutare:** "MySQL"

---

### **2.2. Creează baza de date:**

**În secțiunea "Create New Database":**

**1. Scrie numele bazei:**
```
┌────────────────────────────────────┐
│ New Database                       │
│ username_anunturi  ← SAU marc_anunturi │
└────────────────────────────────────┘
```

**⚠️ IMPORTANT:**
- cPanel adaugă automat un **prefix** (ex: `username_`)
- Numele final va fi: `username_anunturi` sau `marc_anunturi`
- **NOTEAZĂ numele complet!** (îl vei folosi în config.php)

**2. Click "Create Database"**

**✅ Succes!** Ar trebui să vezi: "Added the database username_anunturi"

---

## 🎯 **PASUL 3: Creează Utilizator MySQL (2 minute)**

**Scroll jos în aceeași pagină:**

### **3.1. În secțiunea "Add New User":**

**1. Username:**
```
┌────────────────────────────────────┐
│ Username                           │
│ username_admin  ← SAU marc_admin   │
└────────────────────────────────────┘
```

**2. Password:**
```
┌────────────────────────────────────┐
│ Password                           │
│ ************  ← Parolă puternică!  │
└────────────────────────────────────┘
```

**💡 TIP:** Folosește **"Password Generator"** pentru parolă sigură!

**⚠️ NOTEAZĂ:**
- Username complet (ex: `username_admin`)
- Password

**3. Click "Create User"**

**✅ Succes!** Utilizator creat!

---

## 🎯 **PASUL 4: Asociază Utilizatorul cu Baza de Date (1 minut)**

**Scroll jos în aceeași pagină:**

### **4.1. În secțiunea "Add User To Database":**

**1. Selectează Utilizatorul:**
```
┌────────────────────────────────────┐
│ User:  [username_admin ▼]         │
└────────────────────────────────────┘
```

**2. Selectează Baza de Date:**
```
┌────────────────────────────────────┐
│ Database:  [username_anunturi ▼]  │
└────────────────────────────────────┘
```

**3. Click "Add"**

---

### **4.2. Setează permisiuni:**

**Pe ecranul următor, vezi checkbox-uri cu permisiuni:**

**✅ BIFEAZĂ TOATE PERMISIUNILE!** (sau click pe "ALL PRIVILEGES")

```
[✓] SELECT
[✓] INSERT
[✓] UPDATE
[✓] DELETE
[✓] CREATE
[✓] DROP
[✓] ALTER
[✓] INDEX
... (toate!)
```

**Click "Make Changes"**

**✅ GATA!** Utilizatorul are acces la baza de date!

---

## 🎯 **PASUL 5: Importă Structura Bazei de Date (2 minute)**

### **5.1. Mergi la phpMyAdmin:**

**În cPanel Dashboard, caută:**
```
📊 Databases
   └─ phpMyAdmin  ← CLICK AICI!
```

**SAU caută în bara de căutare:** "phpMyAdmin"

**Se deschide phpMyAdmin în tab nou.**

---

### **5.2. Selectează baza ta:**

**În sidebar stânga:**
```
📂 Databases
   └─ 📁 username_anunturi  ← CLICK!
```

---

### **5.3. Importă SQL:**

**1. Click pe tab-ul "Import" (sus)**

**2. Click "Choose File"**

**3. Selectează fișierul:**
```
database.sql
```
(din folderul proiectului tău)

**4. Scroll jos → Click "Go"**

---

### **5.4. Verifică:**

**Ar trebui să vezi:**
```
✅ Import has been successfully finished, 4 queries executed.
```

**În sidebar stânga, acum vezi tabelele:**
```
📁 username_anunturi
   ├─ 📄 utilizatori
   ├─ 📄 anunturi
   ├─ 📄 mesaje
   └─ 📄 favorite
```

**✅ PERFECT! Baza de date e gata!**

---

## 🎯 **PASUL 6: Notează Detaliile de Conectare**

**NOTEAZĂ ACESTEA (le vei folosi în `api/config.php` pe server!):**

```
Database Host: localhost
Database Name: username_anunturi (sau marc_anunturi)
Database User: username_admin (sau marc_admin)
Database Password: ************ (parola creată)
```

---

## 🎯 **PASUL 7: Configurare pe Server (când uploading files)**

**Când vei uploada fișierele pe server, vei modifica `api/config.php`:**

**LOCAL (XAMPP):**
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'anunturi_db');
```

**LIVE (marc.ro):**
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'username_admin');        // ← Schimbă!
define('DB_PASS', 'parola_ta_sigură');      // ← Schimbă!
define('DB_NAME', 'username_anunturi');     // ← Schimbă!
```

---

## 📂 **Upload Fișiere pe Server (când ești gata)**

### **Opțiunea A - File Manager (cPanel):**

**1. În cPanel, deschide "File Manager"**

**2. Navighează la:**
```
/public_html/
```

**3. Upload fișierele:**
- Drag & drop SAU
- Click "Upload" → Selectează fișiere

**Structura pe server:**
```
/public_html/
├── api/
│   ├── config.php
│   ├── auth.php
│   ├── anunturi.php
│   └── upload.php
├── uploads/  (creat automat)
├── index.html
├── test-php.html
├── styles.css
└── ...
```

---

### **Opțiunea B - FTP/SFTP:**

**Folosește client FTP (FileZilla, WinSCP):**

**Host:** ftp.marc.ro (sau IP server)  
**Username:** (primit de la hosting)  
**Password:** (primit de la hosting)  
**Port:** 21 (FTP) sau 22 (SFTP)

**Upload tot conținutul în `/public_html/`**

---

## 🧪 **Testare pe Server Live**

**După upload, testează:**

```
https://marc.ro/test-php.html
```

**Dacă merge → SUCCES! Site-ul e LIVE! 🎉**

---

## 🆘 **PROBLEME FRECVENTE**

### **Eroare: "Access denied for user"**

**Cauza:** Date de conectare greșite în `config.php`

**Soluție:** Verifică:
- DB_USER = username complet (cu prefix!)
- DB_PASS = parola corectă
- DB_NAME = numele complet al bazei

---

### **Eroare: "Unknown database"**

**Cauza:** Numele bazei e greșit SAU nu ai importat `database.sql`

**Soluție:**
1. Verifică numele exact în phpMyAdmin
2. Re-importă `database.sql` dacă lipsesc tabelele

---

### **Eroare: "Permission denied" la upload imagini**

**Cauza:** Folderul `uploads/` nu există sau nu are permisiuni

**Soluție:**
1. În cPanel File Manager, creează folderul `/public_html/uploads/`
2. Click dreapta pe folder → "Change Permissions" → Setează `755` sau `777`

---

### **Eroare 500 (Internal Server Error)**

**Cauza:** Eroare PHP

**Soluție:**
1. În cPanel, deschide "Error Log"
2. Vezi ultima eroare
3. De obicei e o paranteza lipsă sau path greșit

---

## 📝 **CHECKLIST FINAL - Server LIVE**

- [ ] Bază de date creată în cPanel
- [ ] Utilizator MySQL creat
- [ ] Utilizator asociat cu baza de date (ALL PRIVILEGES)
- [ ] Structura SQL importată (4 tabele)
- [ ] Fișiere uploaded în `/public_html/`
- [ ] `api/config.php` modificat cu date server
- [ ] Testat `https://marc.ro/test-php.html`
- [ ] Înregistrare → funcționează
- [ ] Login → funcționează
- [ ] Publică anunț → funcționează

**✅ TOATE BIFATE = SITE LIVE! 🚀**

---

## 💡 **TIP PRO: Două Configurații**

**Vei avea 2 seturi de fișiere:**

**LOCAL (pentru dezvoltare):**
- Path: `E:\Carte\BB\...`
- DB: `anunturi_db` (XAMPP)
- API URL: `http://localhost/api/`

**LIVE (pentru producție):**
- Path: `/public_html/` (server)
- DB: `username_anunturi` (cPanel MySQL)
- API URL: `https://marc.ro/api/`

**Îți voi crea un sistem care detectează automat! 😉**

---

**SUCCES cu marc.ro! 🎉**

