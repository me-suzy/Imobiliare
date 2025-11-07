# 🔐 Ghid Parole Utilizatori

## ⚠️ IMPORTANT - Securitate

Parolele din baza de date sunt **hash-uite cu bcrypt** pentru securitate. Aceasta este o practică standard - **nu poți "decripta" un hash**, dar poți verifica dacă o parolă introdusă corespunde hash-ului.

## 📋 Parole Standard

Toți utilizatorii de test au parola: **`password`**

### Utilizatori Existente:

1. **test@example.com** (Ionel Bălăuță)
   - Parolă: `password`
   - Tip cont: `user`

2. **admin@marc.ro** (Administrator)
   - Parolă: `password`
   - Tip cont: `admin`

3. **ionel@example.com**
   - Parolă: `password`
   - Tip cont: `user`

4. **eu@example.com**
   - Parolă: `password`
   - Tip cont: `user`

## 🔧 Resetare Parole

### Opțiunea 1: Script Automat (RECOMANDAT)

Accesează: `http://localhost/reset-parole.php`

Acest script te permite să:
- Vezi toți utilizatorii și hash-urile lor
- Resetezi parola oricărui utilizator la `password`
- Creezi parolă nouă pentru orice utilizator

### Opțiunea 2: phpMyAdmin

1. Deschide phpMyAdmin: `http://localhost/phpmyadmin`
2. Selectează baza de date `anunturi_db`
3. Click pe tabelul `utilizatori`
4. Click pe "Edit" pentru utilizatorul dorit
5. În câmpul `parola`, înlocuiește hash-ul cu:
   ```
   $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
   ```
6. Click "Go"
7. Parola utilizatorului va fi: `password`

### Opțiunea 3: Generare Hash Nou

Dacă vrei o parolă diferită, folosește PHP:

```php
<?php
$parola = 'parola_ta_noua';
$hash = password_hash($parola, PASSWORD_BCRYPT);
echo $hash;
?>
```

Sau folosește scriptul `reset-parole.php` care face asta automat.

## 🧪 Testare Login

Pentru a testa login-ul:

1. **Email:** `test@example.com`
2. **Parolă:** `password`

Sau pentru admin:

1. **Email:** `admin@marc.ro`
2. **Parolă:** `password`

## 📝 Notițe

- Hash-ul `$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi` = parola `password`
- Toate hash-urile încep cu `$2y$10$` (bcrypt)
- Nu poți "vedea" parola reală dintr-un hash - aceasta este o măsură de securitate
- La login, sistemul compară parola introdusă cu hash-ul din baza de date folosind `password_verify()`

## 🔒 Securitate

- **NU** stoca parole în text clar
- **NU** partaja fișierul `reset-parole.php` public
- **Șterge** `reset-parole.php` după utilizare pe server-ul de producție
- Folosește parole puternice pentru utilizatori reali

---

**Pentru resetare rapidă:** Accesează `http://localhost/reset-parole.php` 🚀

