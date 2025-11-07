# 🔐 Instalare Sistem Parole Admin

## 📋 Ce face acest sistem?

Permite admin-ului să vadă parolele utilizatorilor în text clar (nu hash-uite). Parolele sunt stocate **criptate** (nu hash-uite), astfel încât pot fi decriptate și afișate.

## ⚠️ ATENȚIE - Securitate

- **NU activa în producție** pentru securitate maximă
- Folosește doar pentru dezvoltare/admin local
- Parolele hash-uite din `utilizatori` rămân pentru login (securitate)
- Parolele criptate din `parole_admin` sunt doar pentru vizualizare admin

## 🚀 Instalare

### Pasul 1: Creează Tabelul

Rulează în phpMyAdmin sau accesează:
```
http://localhost/api/admin-parole.php
```

Script-ul va crea automat tabelul `parole_admin` dacă nu există.

### Pasul 2: Sincronizează Parolele Existente

Prima dată când accesezi API-ul, va sincroniza automat toate parolele existente (va seta "password" pentru toți utilizatorii).

### Pasul 3: Accesează Panoul Admin

```
http://localhost/admin-parole.html
```

## 📝 Utilizare

1. **Vizualizează parolele:**
   - Accesează `admin-parole.html`
   - Vezi toate parolele utilizatorilor în text clar

2. **Editează parola:**
   - Click pe butonul "Editează" pentru orice utilizator
   - Introdu parola nouă
   - Parola va fi actualizată în ambele tabele (hash-uită pentru login, criptată pentru vizualizare)

## 🔧 Configurare

Cheia de criptare este definită în `api/admin-parole.php`:

```php
define('ENCRYPTION_KEY', 'marc_ro_secret_key_2024_change_this!');
```

**IMPORTANT:** Schimbă această cheie pentru securitate!

## 📊 Structura

- **Tabel `utilizatori`:**
  - `parola` - Hash bcrypt (pentru login, securitate)

- **Tabel `parole_admin`:**
  - `parola_criptata` - Parolă criptată AES (pentru vizualizare admin)

## 🔒 Securitate

1. **Pentru login:** Folosește hash-ul bcrypt din `utilizatori.parola`
2. **Pentru vizualizare:** Decriptează parola din `parole_admin.parola_criptata`

## 🎯 Workflow

1. Utilizatorul se înregistrează → Parola este hash-uită pentru `utilizatori` și criptată pentru `parole_admin`
2. Utilizatorul se loghează → Sistemul verifică hash-ul din `utilizatori`
3. Admin-ul vrea să vadă parola → Sistemul decriptează din `parole_admin`

## 🛠️ Dezinstalare

Pentru a dezactiva sistemul:
1. Șterge tabelul `parole_admin` din baza de date
2. Șterge fișierele `admin-parole.html` și `api/admin-parole.php`

---

**Pentru instalare rapidă:** Accesează `http://localhost/api/admin-parole.php` și apoi `http://localhost/admin-parole.html` 🚀

