# 🔧 Rezolvare Problemă Parole Admin

## ❌ Problema

Pagina `admin-parole.html` afișează "Eroare la încărcarea parolelor".

## 🔍 Cauze Posibile

1. **Nu ești autentificat ca admin** - API-ul necesită autentificare admin
2. **Tabelul `parole_admin` nu există** - Trebuie creat
3. **Nu există date în tabelul `parole_admin`** - Trebuie sincronizate
4. **Nu există utilizatori în baza de date** - Trebuie creați

## ✅ Soluție Pas cu Pas

### Pasul 1: Verifică Utilizatori

Accesează:
```
http://localhost/test-admin-parole.php
```

Acest script va afișa:
- ✅ Dacă există utilizatori în baza de date
- ✅ Dacă tabelul `parole_admin` există
- ✅ Dacă există date în `parole_admin`
- ✅ Ce eroare apare la API

### Pasul 2: Sincronizează Parolele

Accesează:
```
http://localhost/sincronizeaza-parole.php
```

Acest script va:
- ✅ Crea tabelul `parole_admin` dacă nu există
- ✅ Sincroniza toate parolele existente
- ✅ Setează parola "password" pentru toți utilizatorii

### Pasul 3: Loghează-te ca Admin

1. Accesează: `http://localhost/login.html`
2. Email: `admin@marc.ro`
3. Parolă: `password`
4. Apasă "Intră în cont"

**IMPORTANT:** Verifică că utilizatorul `admin@marc.ro` există și are `tip_cont = 'admin'`!

### Pasul 4: Verifică Admin Panel

După login, accesează:
```
http://localhost/admin-parole.html
```

Ar trebui să vezi toți utilizatorii cu parolele lor.

## 🛠️ Dacă Nu Funcționează

### Verifică în phpMyAdmin

1. Deschide: `http://localhost/phpmyadmin`
2. Selectează baza de date `anunturi_db`
3. Verifică:
   - Tabelul `utilizatori` - ar trebui să ai utilizatori
   - Tabelul `parole_admin` - ar trebui să existe și să aibă date
   - Utilizatorul `admin@marc.ro` - ar trebui să aibă `tip_cont = 'admin'`

### Creează Utilizator Admin Manual

Dacă nu există utilizator admin, rulează în phpMyAdmin:

```sql
-- Verifică dacă există
SELECT * FROM utilizatori WHERE email = 'admin@marc.ro';

-- Dacă nu există, creează-l
INSERT INTO utilizatori (nume, email, parola, telefon, tip_cont) 
VALUES ('Administrator', 'admin@marc.ro', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0722000000', 'admin');
-- Parola: password
```

### Sincronizează Manual Parolele

Dacă tabelul `parole_admin` există dar e gol, rulează în phpMyAdmin:

```sql
-- Pentru fiecare utilizator, inserează parola criptată
-- (Folosește scriptul sincronizeaza-parole.php pentru asta)
```

## 🎯 Quick Fix

1. Rulează: `http://localhost/test-admin-parole.php` - vezi ce lipsește
2. Rulează: `http://localhost/sincronizeaza-parole.php` - sincronizează parolele
3. Login: `http://localhost/login.html` cu `admin@marc.ro` / `password`
4. Accesează: `http://localhost/admin-parole.html` - ar trebui să funcționeze

---

**Dacă problema persistă, spune-mi ce eroare exactă apare în `test-admin-parole.php`!** 🚀

