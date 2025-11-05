# 🐘 Backend PHP + MySQL

Backend complet pentru platforma de anunțuri, construit cu PHP nativ și MySQL.

---

## 📋 Caracteristici

✅ **API RESTful** cu PHP nativ  
✅ **Autentificare JWT**  
✅ **CRUD Anunțuri**  
✅ **MySQL Database**  
✅ **Upload Imagini**  
✅ **Sistem Mesagerie**  
✅ **Optimizat pentru hosting shared**  

---

## 🛠️ Instalare

### Prerequisite:
- **PHP** (7.4 sau mai nou)
- **MySQL** (5.7 sau mai nou)
- **Apache/Nginx**
- **Composer** (pentru dependențe)

### Pasul 1: Instalează dependențe

```bash
cd backend-php
composer require firebase/php-jwt
```

### Pasul 2: Configurare bază de date

1. **Creează baza de date** în phpMyAdmin sau MySQL Workbench
2. **Rulează** fișierul `database.sql`:

```bash
mysql -u root -p < database.sql
```

Sau în phpMyAdmin:
- Imports → Choose File → `database.sql` → Go

### Pasul 3: Configurare `database.php`

Editează `config/database.php`:

```php
private $host = "localhost";
private $db_name = "anunturi_db";
private $username = "root";       // Username MySQL
private $password = "";            // Parola MySQL
```

### Pasul 4: Configurare Apache/Nginx

**Apache (.htaccess):**
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^api/(.*)$ api/$1.php [L,QSA]
</IfModule>
```

**Nginx:**
```nginx
location /api/ {
    try_files $uri $uri/ /api/$uri.php?$query_string;
}
```

### Pasul 5: Setare permisiuni

```bash
chmod 755 api/
chmod 777 uploads/
```

---

## 📡 API Endpoints

### Base URL:
```
http://localhost/backend-php/api/
```

### **Autentificare**

#### Register
```http
POST /api/auth.php/register
Content-Type: application/json

{
  "name": "Ion Popescu",
  "email": "ion@example.com",
  "password": "parola123",
  "phone": "0722123456"
}
```

#### Login
```http
POST /api/auth.php/login
Content-Type: application/json

{
  "email": "ion@example.com",
  "password": "parola123"
}
```

**Răspuns:**
```json
{
  "message": "Autentificare reușită",
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "user": { ... }
}
```

---

### **Anunțuri**

#### Get All Ads
```http
GET /api/ads.php?page=1&limit=20&category=imobiliare
```

#### Get Single Ad
```http
GET /api/ads.php?id=1
```

#### Create Ad
```http
POST /api/ads.php
Authorization: Bearer YOUR_TOKEN
Content-Type: application/json

{
  "user_id": 1,
  "title": "Apartament 3 camere",
  "description": "Descriere detaliată...",
  "category": "imobiliare",
  "price": 120000,
  "currency": "EUR",
  "location": {
    "city": "București",
    "county": "bucuresti"
  }
}
```

#### Delete Ad
```http
DELETE /api/ads.php?id=1
Authorization: Bearer YOUR_TOKEN
```

---

## 🔗 Conectare cu Frontend

În JavaScript:

```javascript
const API_URL = 'http://localhost/backend-php/api';

// Login
async function login(email, password) {
    const response = await fetch(`${API_URL}/auth.php/login`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ email, password })
    });
    
    const data = await response.json();
    
    if (data.token) {
        localStorage.setItem('token', data.token);
    }
    
    return data;
}

// Get Ads
async function getAds(filters = {}) {
    const queryString = new URLSearchParams(filters).toString();
    const response = await fetch(`${API_URL}/ads.php?${queryString}`);
    return await response.json();
}
```

---

## 📁 Structura Proiectului

```
backend-php/
├── api/
│   ├── auth.php         # Autentificare
│   ├── ads.php          # Anunțuri
│   ├── messages.php     # Mesaje
│   └── users.php        # Utilizatori
├── config/
│   └── database.php     # Conexiune DB
├── models/
│   ├── User.php
│   ├── Ad.php
│   └── Message.php
├── uploads/             # Imagini uploadate
├── composer.json        # Dependențe PHP
├── database.sql         # Script SQL
└── README.md
```

---

## 🚀 Deploy pe Hosting Shared

### Hosting recomandat:
- **InfinityFree** (gratuit)
- **000webhost** (gratuit)
- **Hostinger** (plătit, ~€2/lună)
- **SiteGround** (plătit, premium)

### Pași deploy:

1. **Urcă fișierele** via FTP în `public_html/`
2. **Creează baza de date** în cPanel
3. **Importă** `database.sql`
4. **Modifică** `config/database.php` cu datele de la hosting
5. **Setează** permisiuni folder `uploads/` la 777
6. **Testează** API-ul

---

## 🔐 Securitate

**IMPORTANT pentru producție:**

1. **Schimbă** `SECRET_KEY` din `auth.php`:
```php
$secret_key = "GENEREAZA_UN_STRING_RANDOM_AICI_FOARTE_LUNG";
```

2. **Activează HTTPS**
3. **Validează INPUT** pentru SQL injection
4. **Limitează rate** (max requests/minute)
5. **Folosește** prepared statements (deja implementat)

---

## 🐛 Troubleshooting

### Eroare "Connection failed"
- Verifică datele din `config/database.php`
- Asigură-te că MySQL rulează

### Eroare "Class 'JWT' not found"
```bash
composer install
```

### Permisiuni denied pentru uploads
```bash
chmod 777 uploads/
```

### CORS errors
Adaugă în `.htaccess`:
```apache
Header set Access-Control-Allow-Origin "*"
```

---

## 📝 composer.json

Creează fișierul `composer.json`:

```json
{
    "require": {
        "firebase/php-jwt": "^6.0"
    }
}
```

Apoi rulează:
```bash
composer install
```

---

**Backend PHP este gata de folosit! 🎉**

**Avantaje PHP:**
- ✅ Hosting ieftin și ușor de găsit
- ✅ Nu necesită server dedicat
- ✅ Funcționează pe shared hosting
- ✅ Rapid de configurat

---

**Next:** Conectează frontend-ul și testează! 🚀


