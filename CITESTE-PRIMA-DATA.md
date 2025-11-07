# 🚨 REZOLVARE RAPIDĂ - ERR_CONNECTION_REFUSED

## ❌ **PROBLEMA TA:**

Eroare la `http://localhost/anunturi/test-php.html`:
```
This site can't be reached
localhost refused to connect.
ERR_CONNECTION_REFUSED
```

---

## ✅ **SOLUȚIA (3 minute!):**

### **PASUL 1: Verifică Apache (30 secunde)**

**În XAMPP Control Panel:**

Ar trebui să vezi:
```
Apache  [Stop]  Port: 80, 443  ← VERDE = MERGE!
MySQL   [Stop]  Port: 3306     ← VERDE = MERGE!
```

**DACĂ NU SUNT VERZI:**
- Click pe **"Start"** pentru fiecare
- Așteaptă să devină VERZI

**DACĂ APACHE NU PORNEȘTE (eroare Port 80):**
1. Închide Skype (folosește Port 80)
2. SAU schimbă portul Apache la 8080 (vezi mai jos)

---

### **PASUL 2: Configurează Apache (2 minute)**

**Problema:** Apache nu știe să folosească folderul tău!

**Soluția:**

1. **În XAMPP Control Panel**, click **"Config"** (lângă Apache)
2. Click **"Apache (httpd.conf)"**
3. Se deschide fișierul în Notepad

4. **Caută linia** (Ctrl + F):
   ```
   DocumentRoot "C:/xampp/htdocs"
   ```

5. **ÎNLOCUIEȘTE cu**:
   ```
   DocumentRoot "E:/Carte/BB/17 - Site Leadership/alte/Ionel Balauta/Aryeht/Task 1 - Traduce tot site-ul/Doar Google Web/Andreea/Meditatii/2023/+++Imobiliare"
   ```

6. **Câteva linii mai jos, caută:**
   ```
   <Directory "C:/xampp/htdocs">
   ```

7. **ÎNLOCUIEȘTE cu**:
   ```
   <Directory "E:/Carte/BB/17 - Site Leadership/alte/Ionel Balauta/Aryeht/Task 1 - Traduce tot site-ul/Doar Google Web/Andreea/Meditatii/2023/+++Imobiliare">
   ```

8. **Salvează** (Ctrl + S)

9. **În XAMPP Control Panel**:
   - Click **"Stop"** (Apache)
   - Așteaptă 2 secunde
   - Click **"Start"** (Apache)
   - Ar trebui să fie VERDE!

---

### **PASUL 3: TESTEAZĂ!**

**În browser, mergi la:**
```
http://localhost/test-php.html
```

**AR TREBUI SĂ MEARGĂ ACUM! 🎉**

**DACĂ ÎNCĂ NU MERGE:**
- Verifică că path-ul e EXACT (slash-uri normale `/`)
- Verifică că ai salvat fișierul `httpd.conf`
- Restart Apache din nou

---

## ⚠️ **DACĂ PORT 80 E OCUPAT:**

**Eroare:** "Port 80 in use by System"

**SOLUȚIE - Schimbă portul la 8080:**

1. În `httpd.conf`, caută:
   ```
   Listen 80
   ```

2. Schimbă în:
   ```
   Listen 8080
   ```

3. Salvează și restart Apache

4. **ACUM accesezi:**
   ```
   http://localhost:8080/test-php.html
   ```

---

## 🎯 **DUPĂ CE MERGE:**

### **Continuă cu:**

1. **Creează baza de date:**
   - `http://localhost/phpmyadmin`
   - Import → `database.sql`

2. **Testează backend:**
   - `http://localhost/test-php.html`
   - Înregistrare → Login → Publică anunț

3. **Configurează pentru marc.ro:**
   - Vezi [CPANEL-GHID.md](CPANEL-GHID.md)

---

## 📖 **GHIDURI DISPONIBILE:**

- **CONFIGURARE-XAMPP.md** - Detalii complete configurare Apache
- **START-PHP.md** - Setup complet local (XAMPP + MySQL)
- **CPANEL-GHID.md** - Deploy pe server marc.ro
- **README-MARC.md** - Documentație completă proiect

---

## 🆘 **ÎNCĂ NU MERGE?**

**Verifică Apache Error Log:**

1. În XAMPP Control Panel, click **"Logs"**
2. Click **"Apache (error.log)"**
3. Scroll jos - vezi ultima eroare
4. **Copiază eroarea și spune-mi!**

---

## 💪 **TU POȚI!**

**Doar 3 minute și va merge! 🚀**

**Orice problemă, spune-mi exact ce vezi! 💬**

