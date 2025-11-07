# 🔧 Configurare XAMPP pentru Folderul Tău

## 🎯 **PASUL 1: Verifică că Apache rulează (1 minut)**

### **1.1. Deschide XAMPP Control Panel**

Ar trebui să fie deschis deja. Dacă nu:
```
C:\xampp\xampp-control.exe
```

### **1.2. Verifică statusul:**

**Trebuie să vezi:**
```
Apache  [Start]  ← Dacă scrie Start, CLICK pe el!
MySQL   [Start]  ← Dacă scrie Start, CLICK pe el!
```

**După click, ar trebui să devină:**
```
Apache  [Stop]  Port: 80, 443  ← VERDE = MERGE!
MySQL   [Stop]  Port: 3306     ← VERDE = MERGE!
```

**✅ Dacă sunt VERDE, Apache rulează!**

---

## ⚠️ **PROBLEMĂ: Port 80 ocupat?**

**Dacă vezi eroare la pornire Apache:**
```
Port 80 in use by "System" with PID 4!
```

**SOLUȚIE:**

### **Opțiunea A - Oprește Skype/alte programe:**
1. Închide Skype (folosește Port 80)
2. Închide IIS (dacă e instalat)
3. Restart Apache

### **Opțiunea B - Schimbă portul Apache:**
1. În XAMPP Control Panel, click **"Config"** (lângă Apache)
2. Click **"httpd.conf"**
3. Caută linia: `Listen 80`
4. Schimbă în: `Listen 8080`
5. Salvează
6. Restart Apache
7. **ACUM accesezi:** `http://localhost:8080/test-php.html`

---

## 🎯 **PASUL 2: Configurează DocumentRoot pentru folderul tău (3 minute)**

### **2.1. Deschide fișierul de configurare:**

**În XAMPP Control Panel:**
1. Click butonul **"Config"** (lângă Apache)
2. Click **"Apache (httpd.conf)"**

**Se deschide fișierul în Notepad/editor text**

---

### **2.2. Găsește și modifică DocumentRoot:**

**Caută linia (Ctrl + F):**
```
DocumentRoot "C:/xampp/htdocs"
```

**ÎNLOCUIEȘTE cu:**
```
DocumentRoot "E:/Carte/BB/17 - Site Leadership/alte/Ionel Balauta/Aryeht/Task 1 - Traduce tot site-ul/Doar Google Web/Andreea/Meditatii/2023/+++Imobiliare"
```

**⚠️ ATENȚIE:** Folosește **slash-uri normale** `/` (NU backslash `\`)!

---

### **2.3. Mai jos, găsește și modifică Directory:**

**Caută linia (câteva linii mai jos):**
```
<Directory "C:/xampp/htdocs">
```

**ÎNLOCUIEȘTE cu:**
```
<Directory "E:/Carte/BB/17 - Site Leadership/alte/Ionel Balauta/Aryeht/Task 1 - Traduce tot site-ul/Doar Google Web/Andreea/Meditatii/2023/+++Imobiliare">
```

**ACELAȘI PATH ca mai sus!**

---

### **2.4. Salvează fișierul:**

**Ctrl + S** sau **File → Save**

---

### **2.5. Restart Apache:**

**În XAMPP Control Panel:**
1. Click **"Stop"** (lângă Apache)
2. Așteaptă 2 secunde
3. Click **"Start"**

**Ar trebui să devină VERDE din nou!** ✅

---

## 🧪 **PASUL 3: TESTEAZĂ!**

**Acum, în browser, mergi la:**
```
http://localhost/test-php.html
```

**AR TREBUI SĂ MEARGĂ! 🎉**

---

## 🆘 **DACĂ ÎNCĂ NU MERGE:**

### **Verifică Apache Error Log:**

**În XAMPP Control Panel:**
1. Click butonul **"Logs"** (lângă Apache)
2. Click **"Apache (error.log)"**
3. Scroll jos - Vezi ultima eroare

**Copiază eroarea și spune-mi ce scrie!**

---

### **Verifică că path-ul e corect:**

**Deschide Windows Explorer și navighează la:**
```
E:\Carte\BB\17 - Site Leadership\alte\Ionel Balauta\Aryeht\Task 1 - Traduce tot site-ul\Doar Google Web\Andreea\Meditatii\2023\+++Imobiliare\test-php.html
```

**Există fișierul? Dacă DA → Path-ul e corect!**

---

## ✅ **DUPĂ CE MERGE:**

**Vei putea accesa:**
- `http://localhost/test-php.html` ← Test backend
- `http://localhost/index.html` ← Homepage
- `http://localhost/api/auth.php` ← API autentificare
- `http://localhost/api/anunturi.php` ← API anunțuri

**Totul rulează din folderul tău direct!** 🚀

---

## 📝 **NOTĂ IMPORTANTĂ:**

Când configurezi pentru **marc.ro** pe server, vei avea **2 configurații:**

**LOCAL (dezvoltare):**
```javascript
const API_URL = 'http://localhost/api/';
```

**LIVE (producție):**
```javascript
const API_URL = 'https://marc.ro/api/';
```

**Îți voi crea un sistem care detectează automat! 😉**

