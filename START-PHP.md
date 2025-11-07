# 🚀 Ghid RAPID - PHP Backend

**Timp estimat: 10 minute!**

---

## ✅ **PASUL 1: Pornește XAMPP (2 minute)**

### **1.1. Pornește XAMPP Control Panel:**

**Dublu-click pe:**
```
c:\xampp\xampp_start.exe
```

**SAU:**

Deschide **XAMPP Control Panel** din Start Menu

---

### **1.2. Pornește Apache și MySQL:**

**În XAMPP Control Panel, click pe "Start" pentru:**

✅ **Apache** (server web pentru PHP)  
✅ **MySQL** (bază de date)

**Ar trebui să vezi:**
```
Apache  [Running]  ← Verde
MySQL   [Running]  ← Verde
```

**✅ PASUL 1 COMPLET!**

---

## 💾 **PASUL 2: Creează Baza de Date (3 minute)**

### **2.1. Deschide phpMyAdmin:**

**În browser, mergi la:**
```
http://localhost/phpmyadmin
```

---

### **2.2. Importă baza de date:**

**Opțiunea A - Import SQL (RECOMANDAT):**

1. Click pe tab-ul **"SQL"** (sus)
2. Deschide fișierul **`database.sql`** din proiect (în Notepad)
3. **Copiază TOT** conținutul
4. **Lipește** în zona mare de text din phpMyAdmin
5. Click **"Go"** (jos, dreapta)
6. ✅ **Succes!** Ar trebui să vezi: "4 tables created"

**Opțiunea B - Import fișier:**

1. Click pe tab-ul **"Import"** (sus)
2. Click **"Choose File"** → Selectează **`database.sql`**
3. Scroll jos → Click **"Go"**
4. ✅ **Succes!**

---

### **2.3. Verifică:**

**În phpMyAdmin, sidebar stânga:**
```
📂 Databases
   └─ 📁 anunturi_db  ← CLICK!
       ├─ 📄 utilizatori
       ├─ 📄 anunturi
       ├─ 📄 mesaje
       └─ 📄 favorite
```

**Ar trebui să vezi 4 tabele!** ✅

**✅ PASUL 2 COMPLET!**

---

## 📁 **PASUL 3: Copiază Proiectul în htdocs (2 minute)**

### **3.1. Găsește folderul proiectului tău:**

Ești acum în:
```
E:\Carte\BB\17 - Site Leadership\alte\Ionel Balauta\Aryeht\Task 1 - Traduce tot site-ul\Doar Google Web\Andreea\Meditatii\2023\+++Imobiliare
```

---

### **3.2. OPȚIUNE A - Lucrează direct din acest folder:**

**Configurare rapidă:**

1. Deschide **XAMPP Control Panel**
2. Click **"Config"** (butonul de lângă Apache) → **"httpd.conf"**
3. Caută linia: `DocumentRoot "C:/xampp/htdocs"`
4. **Înlocuiește** cu:
   ```
   DocumentRoot "E:/Carte/BB/17 - Site Leadership/alte/Ionel Balauta/Aryeht/Task 1 - Traduce tot site-ul/Doar Google Web/Andreea/Meditatii/2023/+++Imobiliare"
   ```
5. Caută linia: `<Directory "C:/xampp/htdocs">`
6. **Înlocuiește** cu același path
7. **Salvează** fișierul
8. În XAMPP Control Panel: **Stop Apache** → **Start Apache**

**ACUM poți accesa site-ul la:** `http://localhost/`

---

### **3.3. OPȚIUNE B - Copiază în htdocs (MAI SIMPLU!):**

1. **Copiază tot folderul** proiectului tău
2. **Lipește** în: `C:\xampp\htdocs\`
3. **Redenumește** folderul în: `anunturi`

**ACUM poți accesa site-ul la:** `http://localhost/anunturi/`

**Exemplu:**
```
C:\xampp\htdocs\anunturi\
├── 📁 api\
│   ├── config.php
│   ├── auth.php
│   ├── anunturi.php
│   └── upload.php
├── 📁 uploads\
├── 📄 index.html
├── 📄 test-php.html
└── ...
```

**✅ PASUL 3 COMPLET!**

---

## 🧪 **PASUL 4: TESTEAZĂ! (3 minute)**

### **4.1. Deschide pagina de test:**

**În browser, mergi la:**

**Dacă ai folosit Opțiunea A (config httpd.conf):**
```
http://localhost/test-php.html
```

**Dacă ai folosit Opțiunea B (copiat în htdocs):**
```
http://localhost/anunturi/test-php.html
```

---

### **4.2. Testează Înregistrare:**

1. **Lasă datele default** (sau schimbă email-ul dacă testezi a 2-a oară)
2. Click **"Înregistrează-te"**
3. **Ar trebui să vezi:**
   ```
   ✅ Cont creat cu succes!
   ```
4. **Jos apare JSON** cu datele utilizatorului

---

### **4.3. Testează Login:**

1. Click **"Login"**
2. **Ar trebui să vezi:**
   ```
   ✅ Autentificare reușită! - Test User
   ```

---

### **4.4. Testează Verificare Sesiune:**

1. Click **"Verifică dacă ești autentificat"**
2. **Ar trebui să vezi:**
   ```
   ✅ Autentificat ca: Test User
   ```

---

### **4.5. Testează Publică Anunț:**

1. **Lasă datele default**
2. Click **"Publică Anunț"**
3. **Ar trebui să vezi:**
   ```
   ✅ Anunț publicat cu succes! (ID: 1)
   ```

---

### **4.6. Testează Vezi Anunțuri:**

1. Click **"Încarcă Anunțuri"**
2. **Ar trebui să vezi:**
   ```
   ✅ Încărcat 1 anunțuri
   ```
3. **Jos apare JSON** cu lista anunțurilor

---

### **4.7. Verifică în phpMyAdmin:**

**Mergi la:** `http://localhost/phpmyadmin`

**Click pe:**
```
📁 anunturi_db
   ├─ 📄 utilizatori  ← CLICK! (ar trebui să vezi 1 utilizator)
   └─ 📄 anunturi     ← CLICK! (ar trebui să vezi 1 anunț)
```

**✅ VEZI DATELE?** → **TOTUL MERGE PERFECT! 🎉**

---

## 🎯 **URMĂTORII PAȘI:**

### **Acum că PHP merge, poți:**

1. **Integrează API-ul în paginile tale HTML:**
   - Modifică `script.js` să folosească `fetch('api/auth.php', ...)`
   - Conectează formularul de login la API
   - Conectează formularul de publicare anunț la API

2. **Testează upload imagini:**
   - Adaugă input file în formular
   - Trimite cu `FormData` la `api/upload.php`

3. **Creează pagini pentru:**
   - Lista anunțuri (citește din `api/anunturi.php`)
   - Detalii anunț (citește din `api/anunturi.php?id=1`)
   - Dashboard utilizator (anunțurile mele)

---

## 📋 **STRUCTURA FINALĂ:**

```
Proiectul tău/
├── 📁 api/
│   ├── config.php         ← Configurare DB + funcții helper
│   ├── auth.php          ← Register, Login, Logout, Check
│   ├── anunturi.php      ← CRUD anunțuri
│   └── upload.php        ← Upload imagini
├── 📁 uploads/           ← Imagini încărcate (create automat)
├── 📄 database.sql       ← Script creare bază de date
├── 📄 test-php.html      ← Pagină test API
├── 📄 index.html         ← Homepage
├── 📄 anunturi.html      ← Lista anunțuri
├── 📄 publica-anunt.html ← Formular publicare
├── 📄 script.js          ← JavaScript (va folosi API-urile)
└── ...
```

---

## 🆘 **PROBLEME FRECVENTE:**

### **Eroare: "Access to fetch blocked by CORS"**

**Soluție:** Asigură-te că accesezi prin `http://localhost/` (nu prin `file://`)

---

### **Eroare: "Connection refused"**

**Soluție:** Verifică că Apache și MySQL sunt pornite în XAMPP (verde!)

---

### **Eroare: "Access denied for user 'root'@'localhost'"**

**Soluție:** 
1. Deschide `api/config.php`
2. Verifică linia 15: `define('DB_PASS', '');` (ar trebui să fie goală în XAMPP!)

---

### **Eroare: "Unknown database 'anunturi_db'"**

**Soluție:** Repetă PASUL 2 - Creează baza de date!

---

### **Pagina nu se încarcă / 404**

**Soluție:** 
- Verifică că proiectul e în `C:\xampp\htdocs\anunturi\`
- Accesezi corect: `http://localhost/anunturi/test-php.html`

---

## 🏆 **SUCCES!**

**Ai acum:**
✅ Server PHP funcțional (Apache)  
✅ Bază de date MySQL funcțională  
✅ API-uri pentru autentificare  
✅ API-uri pentru anunțuri  
✅ Upload imagini funcțional  
✅ Tot sub CONTROLUL TĂU! 💪

**NU mai depinzi de servicii externe!**  
**NU mai ai setări complicate!**  
**Totul e LOCAL și SIMPLU!**

---

## 💰 **COST:**

✅ **XAMPP:** GRATUIT  
✅ **PHP:** GRATUIT  
✅ **MySQL:** GRATUIT  
✅ **Hosting (mai târziu):** 100-300 RON/an  

---

## 📞 **AJUTOR:**

**Dacă ceva nu merge:**
1. Verifică că Apache și MySQL sunt **VERDE** în XAMPP
2. Verifică că ai rulat **database.sql** în phpMyAdmin
3. Verifică că proiectul e în **htdocs** SAU ai modificat **httpd.conf**
4. Deschide **Developer Tools** (F12) în browser → Tab **Console**
5. Verifică erorile PHP în: `C:\xampp\apache\logs\error.log`

**Spune-mi exact ce eroare vezi și te ajut!** 💪

---

**GATA! Acum construiește site-ul de vis! 🚀**

