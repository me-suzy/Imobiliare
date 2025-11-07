# 🚀 Marc.ro - Portal Anunțuri Gratuite

**Backend:** PHP + MySQL  
**Frontend:** HTML5 + CSS3 + JavaScript  
**Local:** XAMPP  
**Live:** marc.ro (hosting cPanel)

---

## 📋 **CUPRINS**

1. [Setup Local (XAMPP)](#setup-local)
2. [Configurare XAMPP pentru folderul tău](#configurare-xampp)
3. [Setup Server Live (marc.ro)](#setup-server-live)
4. [Structura Proiectului](#structura)
5. [API Documentation](#api-documentation)
6. [Troubleshooting](#troubleshooting)

---

## 🏠 **1. SETUP LOCAL (XAMPP)** {#setup-local}

### **Pași rapizi:**

1. **Pornește XAMPP Control Panel:**
   ```
   C:\xampp\xampp-control.exe
   ```

2. **Start Apache și MySQL** (trebuie să fie VERDE!)

3. **Creează baza de date:**
   - Mergi la: `http://localhost/phpmyadmin`
   - Click tab "SQL"
   - Copiază conținutul din `database.sql`
   - Paste și click "Go"
   - ✅ Baza `anunturi_db` cu 4 tabele creată!

4. **Configurează XAMPP să folosească folderul tău**
   - Vezi [CONFIGURARE-XAMPP.md](CONFIGURARE-XAMPP.md) pentru pași detaliați

5. **Testează:**
   - Mergi la: `http://localhost/test-php.html`
   - Înregistrează cont → Login → Publică anunț
   - ✅ Dacă merge = SUCCESS!

**📖 Ghid detaliat:** [START-PHP.md](START-PHP.md)

---

## ⚙️ **2. CONFIGURARE XAMPP PENTRU FOLDERUL TĂU** {#configurare-xampp}

**PROBLEMĂ:** `http://localhost/anunturi/test-php.html` → ERR_CONNECTION_REFUSED

**SOLUȚIE:** Configurează Apache să folosească folderul tău direct!

### **Pași:**

1. **Deschide XAMPP Control Panel**

2. **Click "Config" (lângă Apache) → "Apache (httpd.conf)"**

3. **Caută și modifică linia:**
   ```apache
   DocumentRoot "C:/xampp/htdocs"
   ```
   
   **Înlocuiește cu:**
   ```apache
   DocumentRoot "E:/Carte/BB/17 - Site Leadership/alte/Ionel Balauta/Aryeht/Task 1 - Traduce tot site-ul/Doar Google Web/Andreea/Meditatii/2023/+++Imobiliare"
   ```

4. **Mai jos, caută și modifică:**
   ```apache
   <Directory "C:/xampp/htdocs">
   ```
   
   **Înlocuiește cu:**
   ```apache
   <Directory "E:/Carte/BB/17 - Site Leadership/alte/Ionel Balauta/Aryeht/Task 1 - Traduce tot site-ul/Doar Google Web/Andreea/Meditatii/2023/+++Imobiliare">
   ```

5. **Salvează fișierul** (Ctrl + S)

6. **Restart Apache** (Stop → Start în XAMPP Control Panel)

7. **Testează:**
   ```
   http://localhost/test-php.html
   ```
   **AR TREBUI SĂ MEARGĂ ACUM! ✅**

**⚠️ NOTĂ:** Folosește slash-uri normale `/` (NU backslash `\`) în path!

**📖 Ghid detaliat:** [CONFIGURARE-XAMPP.md](CONFIGURARE-XAMPP.md)

---

## 🌐 **3. SETUP SERVER LIVE (marc.ro)** {#setup-server-live}

### **Pregătire pentru deploy:**

1. **Creează baza de date în cPanel:**
   - Login la cPanel (https://marc.ro:2083)
   - MySQL® Databases → Create New Database
   - Notează: numele complet (ex: `username_anunturi`)
   
2. **Creează utilizator MySQL:**
   - Add New User
   - Notează: username și password
   
3. **Asociază user cu baza de date:**
   - Add User To Database
   - Selectează ALL PRIVILEGES
   
4. **Importă structura:**
   - phpMyAdmin → Import → Selectează `database.sql`

5. **Modifică configurația pentru server:**
   - Redenumește `api/config.php` în `api/config.local.php` (backup)
   - Redenumește `api/config.server.php` în `api/config.php`
   - Deschide `api/config.php` și completează:
     ```php
     define('DB_USER', 'username_admin');     // Din cPanel
     define('DB_PASS', 'parola_ta_sigura');   // Din cPanel
     define('DB_NAME', 'username_anunturi');  // Din cPanel
     ```

6. **Upload fișiere:**
   - cPanel File Manager → `/public_html/`
   - Upload tot conținutul proiectului

7. **Testează:**
   ```
   https://marc.ro/test-php.html
   ```

**📖 Ghid detaliat:** [CPANEL-GHID.md](CPANEL-GHID.md)

---

## 📁 **4. STRUCTURA PROIECTULUI** {#structura}

```
Proiect Marc.ro/
│
├── 📁 api/                          # Backend PHP
│   ├── config.php                   # Configurare DB (LOCAL - XAMPP)
│   ├── config.server.php            # Configurare DB (LIVE - marc.ro)
│   ├── auth.php                     # API Autentificare
│   ├── anunturi.php                 # API Anunțuri (CRUD)
│   └── upload.php                   # API Upload Imagini
│
├── 📁 js/                           # JavaScript
│   └── config.js                    # Configurare API (detectare auto LOCAL/LIVE)
│
├── 📁 css/                          # Stiluri
│   └── styles.css                   # Stiluri principale
│
├── 📁 uploads/                      # Imagini încărcate (creat automat)
│
├── 📁 images/                       # Imagini statice (logo, icons)
│
├── 📄 database.sql                  # Script creare bază de date
│
├── 📄 test-php.html                 # Pagină de test backend
│
├── 📄 index.html                    # Homepage
├── 📄 anunturi.html                 # Lista anunțuri
├── 📄 anunt-detalii.html            # Detalii anunț
├── 📄 publica-anunt.html            # Formular publicare
├── 📄 contul-meu.html               # Dashboard utilizator
├── 📄 cautare.html                  # Căutare avansată
├── 📄 mesaje.html                   # Sistem mesagerie
├── 📄 favorite.html                 # Anunțuri favorite
│
├── 📄 README-MARC.md                # Acest fișier
├── 📄 START-PHP.md                  # Ghid setup local
├── 📄 CONFIGURARE-XAMPP.md          # Configurare Apache
└── 📄 CPANEL-GHID.md                # Ghid deploy server
```

---

## 🔌 **5. API DOCUMENTATION** {#api-documentation}

### **Autentificare:**

#### **POST /api/auth.php - Register**
```json
{
  "action": "register",
  "nume": "Ion Popescu",
  "email": "ion@example.com",
  "parola": "parola123",
  "telefon": "0722123456"
}
```

#### **POST /api/auth.php - Login**
```json
{
  "action": "login",
  "email": "ion@example.com",
  "parola": "parola123"
}
```

#### **GET /api/auth.php?action=check**
Verifică dacă utilizatorul e autentificat.

#### **POST /api/auth.php - Logout**
```json
{
  "action": "logout"
}
```

---

### **Anunțuri:**

#### **GET /api/anunturi.php**
Preia lista anunțuri (cu filtre opționale):
```
/api/anunturi.php?categorie=Imobiliare&pret_min=50000&pret_max=100000&oras=București
```

#### **GET /api/anunturi.php?id=123**
Preia detalii anunț specific.

#### **POST /api/anunturi.php**
Creează anunț nou (necesită autentificare):
```json
{
  "titlu": "Vând apartament 3 camere",
  "descriere": "Apartament spațios...",
  "categorie": "Imobiliare",
  "pret": 85000,
  "moneda": "RON",
  "oras": "București",
  "judet": "București",
  "imagini": ["uploads/img_123.jpg", "uploads/img_124.jpg"]
}
```

#### **PUT /api/anunturi.php**
Actualizează anunț (necesită autentificare):
```json
{
  "id": 123,
  "titlu": "Vând apartament 3 camere - Preț redus!",
  "descriere": "...",
  "pret": 80000
}
```

#### **DELETE /api/anunturi.php?id=123**
Șterge anunț (necesită autentificare).

---

### **Upload Imagini:**

#### **POST /api/upload.php**
Upload imagini (multipart/form-data, necesită autentificare):
```javascript
const formData = new FormData();
formData.append('imagini[]', file1);
formData.append('imagini[]', file2);

fetch('/api/upload.php', {
    method: 'POST',
    body: formData,
    credentials: 'include'
});
```

**Response:**
```json
{
  "success": true,
  "mesaj": "Imagini încărcate cu succes!",
  "imagini": ["uploads/img_123.jpg", "uploads/img_124.jpg"],
  "erori": []
}
```

---

## 🔧 **6. UTILIZARE config.js** 

Fișierul `js/config.js` detectează automat dacă rulezi LOCAL sau pe SERVER LIVE!

### **Folosire în paginile tale:**

```html
<!-- Include config.js în toate paginile -->
<script src="js/config.js"></script>

<script>
    // API_CONFIG.BASE_URL = 'http://localhost/api/' (local)
    //                    SAU 'https://marc.ro/api/' (live)
    
    // Autentificare
    async function login() {
        const result = await Auth.login('email@example.com', 'parola');
        if (result.success) {
            console.log('Logat!', result.user);
        }
    }
    
    // Preia anunțuri
    async function getAnunturi() {
        const result = await Anunturi.getAll({
            categorie: 'Imobiliare',
            pret_max: 100000
        });
        console.log('Anunțuri:', result.anunturi);
    }
    
    // Upload imagini
    async function uploadImages(files) {
        const result = await API.uploadFiles(files);
        console.log('URL-uri imagini:', result.imagini);
    }
</script>
```

**Funcții disponibile:**
- `Auth.register(nume, email, parola, telefon)`
- `Auth.login(email, parola)`
- `Auth.logout()`
- `Auth.check()`
- `Anunturi.getAll(filters)`
- `Anunturi.get(id)`
- `Anunturi.create(anuntData)`
- `Anunturi.update(id, updates)`
- `Anunturi.delete(id)`
- `API.uploadFiles(files)`
- `Utils.formatPrice(price, currency)`
- `Utils.formatDate(dateString)`

---

## 🆘 **7. TROUBLESHOOTING** {#troubleshooting}

### **Problemă: "localhost refused to connect"**

**Cauza:** Apache nu rulează SAU nu e configurat corect.

**Soluție:**
1. Verifică că Apache e VERDE în XAMPP Control Panel
2. Configurează `httpd.conf` să folosească folderul tău
3. Vezi [CONFIGURARE-XAMPP.md](CONFIGURARE-XAMPP.md)

---

### **Problemă: "Access denied for user 'root'@'localhost'"**

**Cauza:** Configurare greșită bază de date în `config.php`.

**Soluție:**
1. Verifică că MySQL e pornit în XAMPP (VERDE)
2. Verifică `api/config.php`:
   ```php
   define('DB_USER', 'root');
   define('DB_PASS', '');  // Goală în XAMPP!
   ```

---

### **Problemă: "Unknown database 'anunturi_db'"**

**Cauza:** Baza de date nu e creată.

**Soluție:**
1. Mergi la `http://localhost/phpmyadmin`
2. Importă `database.sql`

---

### **Problemă: "CORS error"**

**Cauza:** Accesezi prin `file://` în loc de `http://localhost/`.

**Soluție:**
1. Asigură-te că accesezi: `http://localhost/test-php.html`
2. NU deschide direct fișierul HTML (dublu-click)!

---

### **Problemă: Upload imagini nu merge**

**Cauza:** Folderul `uploads/` nu există sau nu are permisiuni.

**Soluție LOCAL:**
1. Creează manual folderul `uploads/` în rădăcina proiectului

**Soluție SERVER:**
1. cPanel File Manager → Creează folder `/public_html/uploads/`
2. Click dreapta → Change Permissions → `755` sau `777`

---

## 📝 **WORKFLOW DEZVOLTARE**

### **Local (XAMPP):**

1. Modifici fișierele în folderul tău
2. Testezi la: `http://localhost/test-php.html`
3. Verifici că totul merge
4. Repeat!

### **Deploy pe Server (marc.ro):**

1. **Înainte de upload:**
   - Backup `api/config.php` → `api/config.local.php`
   - Copiază `api/config.server.php` → `api/config.php`
   - Editează `api/config.php` cu datele din cPanel

2. **Upload:**
   - cPanel File Manager → Upload fișiere în `/public_html/`
   - SAU FTP/SFTP

3. **Testează:**
   - `https://marc.ro/test-php.html`

4. **După testare:**
   - Restaurează `api/config.local.php` → `api/config.php` (pentru local)

---

## 🎯 **NEXT STEPS**

- [ ] Configurează XAMPP pentru folderul tău
- [ ] Testează local (`http://localhost/test-php.html`)
- [ ] Integrează API-urile în paginile HTML
- [ ] Creează formular login funcțional
- [ ] Creează formular publicare anunț funcțional
- [ ] Testează upload imagini
- [ ] Creează baza de date în cPanel (marc.ro)
- [ ] Deploy pe server
- [ ] Testează live (`https://marc.ro/test-php.html`)
- [ ] 🚀 LAUNCH!

---

## 💪 **SUCCES!**

**Ai toate instrumentele necesare!**

**Întrebări? Probleme? Spune-mi! 🔥**

---

**© 2025 Marc.ro - Portal Anunțuri Gratuite**

