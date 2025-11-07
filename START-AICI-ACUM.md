# 🚀 START AICI - Marc.ro Backend PHP

## 🎯 **CE AI ACUM:**

✅ **Backend PHP complet funcțional:**
   - `api/auth.php` - Înregistrare, Login, Logout
   - `api/anunturi.php` - CRUD anunțuri
   - `api/upload.php` - Upload imagini
   - `api/config.php` - Configurare LOCAL (XAMPP)
   - `api/config.server.php` - Configurare LIVE (marc.ro)

✅ **Bază de date MySQL:**
   - `database.sql` - 4 tabele (utilizatori, anunțuri, mesaje, favorite)

✅ **Sistem detectare automată LOCAL/LIVE:**
   - `js/config.js` - Detectează automat unde rulează (localhost sau marc.ro)

✅ **Pagini frontend:**
   - `index.html` - Homepage
   - `test-php.html` - Test backend
   - + alte 10+ pagini HTML

✅ **Ghiduri complete:**
   - `CITESTE-PRIMA-DATA.md` ← **UITE AICI ACUM!**
   - `CONFIGURARE-XAMPP.md` - Rezolvă eroarea localhost
   - `START-PHP.md` - Setup complet local
   - `CPANEL-GHID.md` - Deploy pe marc.ro
   - `README-MARC.md` - Documentație completă

---

## 🔴 **PROBLEMA TA ACTUALĂ:**

**Eroare:** `http://localhost/anunturi/test-php.html` → ERR_CONNECTION_REFUSED

**Cauza:** Apache nu știe să folosească folderul tău!

---

## ✅ **SOLUȚIA (3 pași simpli):**

### **1️⃣ Verifică că Apache și MySQL rulează (30 sec)**

În **XAMPP Control Panel**, trebuie să fie **VERDE**:
```
Apache  [Stop]  Port: 80, 443  ← VERDE!
MySQL   [Stop]  Port: 3306     ← VERDE!
```

**Dacă NU sunt verzi** → Click **"Start"**

---

### **2️⃣ Configurează Apache să folosească folderul tău (2 min)**

**📖 Deschide fișierul:**
```
CITESTE-PRIMA-DATA.md
```

**Urmează EXACT pașii de acolo!**

**Pe scurt:**
1. XAMPP Control Panel → Config (Apache) → httpd.conf
2. Caută `DocumentRoot "C:/xampp/htdocs"`
3. Înlocuiește cu path-ul tău complet
4. Caută `<Directory "C:/xampp/htdocs">`
5. Înlocuiește cu același path
6. Salvează
7. Restart Apache

---

### **3️⃣ Testează (30 sec)**

**Mergi la:**
```
http://localhost/test-php.html
```

**AR TREBUI SĂ MEARGĂ! ✅**

---

## 📋 **DUPĂ CE MERGE:**

### **1. Creează baza de date (3 min)**

```
http://localhost/phpmyadmin
```

- Tab "SQL"
- Copiază conținutul din `database.sql`
- Paste și click "Go"
- ✅ Baza creată!

---

### **2. Testează backend (5 min)**

```
http://localhost/test-php.html
```

**Testează:**
- ✅ Înregistrare utilizator
- ✅ Login
- ✅ Publică anunț
- ✅ Vezi anunțuri

**DACĂ TOATE MERG → BACKEND FUNCȚIONAL! 🎉**

---

## 🌐 **DEPLOY PE MARC.RO (când ești gata)**

**📖 Ghidul complet:**
```
CPANEL-GHID.md
```

**Pe scurt:**
1. Creează baza de date în cPanel (5 min)
2. Modifică `api/config.php` cu datele din cPanel
3. Upload fișiere în `/public_html/`
4. Testează: `https://marc.ro/test-php.html`
5. ✅ SITE LIVE!

---

## 📁 **FIȘIERE IMPORTANTE:**

| Fișier | Descriere |
|--------|-----------|
| **CITESTE-PRIMA-DATA.md** | **👈 CITEȘTE ASTA ACUM!** |
| **CONFIGURARE-XAMPP.md** | Rezolvă ERR_CONNECTION_REFUSED |
| **START-PHP.md** | Setup complet local |
| **CPANEL-GHID.md** | Deploy pe marc.ro |
| **README-MARC.md** | Documentație API + Structură |
| **test-php.html** | Test backend (înregistrare, login, anunțuri) |
| **js/config.js** | Configurare API (detectare auto LOCAL/LIVE) |
| **api/config.php** | Configurare DB pentru LOCAL (XAMPP) |
| **api/config.server.php** | Configurare DB pentru LIVE (marc.ro) |
| **database.sql** | Script creare bază de date |

---

## 🎯 **CHECKLIST RAPID:**

### **LOCAL (acum):**

- [ ] XAMPP Apache și MySQL pornite (VERDE)
- [ ] Apache configurat pentru folderul tău (httpd.conf)
- [ ] Baza de date `anunturi_db` creată (phpmyadmin)
- [ ] `http://localhost/test-php.html` merge
- [ ] Testată înregistrare → ✅
- [ ] Testat login → ✅
- [ ] Testat publică anunț → ✅

**✅ TOATE BIFATE = BACKEND LOCAL FUNCȚIONAL!**

---

### **LIVE (mai târziu, când ești gata):**

- [ ] Bază de date creată în cPanel (marc.ro)
- [ ] Utilizator MySQL creat și asociat
- [ ] Structura SQL importată (database.sql)
- [ ] `api/config.php` modificat cu date cPanel
- [ ] Fișiere uploaded în `/public_html/`
- [ ] `https://marc.ro/test-php.html` merge
- [ ] Testată înregistrare pe LIVE → ✅
- [ ] Testat login pe LIVE → ✅
- [ ] Testat publică anunț pe LIVE → ✅

**✅ TOATE BIFATE = SITE LIVE PE MARC.RO! 🎉**

---

## 🆘 **PROBLEME?**

**Apache nu pornește?**
→ Vezi [CITESTE-PRIMA-DATA.md](CITESTE-PRIMA-DATA.md) - Secțiunea "Port 80 ocupat"

**Eroare bază de date?**
→ Verifică că ai creat baza în phpMyAdmin

**API nu răspunde?**
→ Verifică Apache Error Log (XAMPP → Logs → error.log)

**Altceva?**
→ **Spune-mi EXACT ce eroare vezi! 💬**

---

## 💡 **TIPS:**

✅ **Detectare automată LOCAL vs LIVE:**
   - `js/config.js` detectează singur unde rulează
   - LOCAL → folosește `http://localhost/api/`
   - LIVE → folosește `https://marc.ro/api/`
   - **NU trebuie să schimbi nimic în frontend!** 🎉

✅ **Două configurații separate:**
   - `api/config.php` → Pentru LOCAL (XAMPP)
   - `api/config.server.php` → Pentru LIVE (marc.ro)
   - Când deploying pe server, redenumești `config.server.php` în `config.php`

✅ **Testare ușoară:**
   - `test-php.html` testează toate funcțiile backend
   - Folosește asta înainte de deploy pe server!

---

## 🚀 **ACTION PLAN:**

### **ACUM (următoarele 10 minute):**

1. **📖 Deschide:** `CITESTE-PRIMA-DATA.md`
2. **⚙️ Configurează Apache** (urmează pașii exacti)
3. **🧪 Testează:** `http://localhost/test-php.html`
4. **✅ Verifică:** Înregistrare + Login merg

---

### **AZI (după ce merge local):**

5. **💾 Creează baza de date** în phpMyAdmin
6. **🧪 Testează toate funcțiile** în test-php.html
7. **🎨 Conectează frontend-ul** la backend (formular login, etc.)

---

### **MÂINE (când ești gata):**

8. **🌐 Creează baza de date în cPanel** (marc.ro)
9. **📤 Upload fișiere pe server**
10. **🧪 Testează LIVE:** `https://marc.ro/test-php.html`
11. **🎉 LAUNCH marc.ro!**

---

## 💪 **TU POȚI!**

**Backend-ul e gata! Acum doar configurare XAMPP și merge! 🚀**

**Orice problemă, spune-mi! 💬**

---

**👉 NEXT: Deschide `CITESTE-PRIMA-DATA.md` și urmează pașii! 👈**

