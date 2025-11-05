# 🛍️ Anunțuri & Oferte - Website Complet cu Anunțuri

Un website modern și complet pentru publicarea și gestionarea anunțurilor online, similar cu OLX. Proiectul este construit cu HTML5, CSS3 și JavaScript vanilla, fiind ușor de personalizat și de integrat cu un backend.

---

## 📋 Cuprins

1. [Despre Proiect](#despre-proiect)
2. [Sugestii Nume de Brand](#sugestii-nume-de-brand)
3. [Caracteristici](#caracteristici)
4. [Structura Proiectului](#structura-proiectului)
5. [Instalare și Rulare](#instalare-și-rulare)
6. [Pagini Disponibile](#pagini-disponibile)
7. [Personalizare](#personalizare)
8. [Design și UX](#design-și-ux)
9. [Funcționalități JavaScript](#funcționalități-javascript)
10. [Integrare Backend](#integrare-backend)
11. [Browser Support](#browser-support)
12. [Licență](#licență)

---

## 🎯 Despre Proiect

Acest proiect oferă o platformă completă pentru marketplace-ul de anunțuri online cu:
- Design modern și responsive
- Interfață intuitivă pentru utilizatori
- Sistem complet de gestionare anunțuri
- Funcționalități de căutare și filtrare avansate
- Sistem de mesagerie
- Gestionare cont utilizator
- Favorite și notificări

---

## 🏷️ Sugestii Nume de Brand

În cazul în care **BEBE.ro** nu este ideală (datorită asocierii cu bebeluși), iată 10 sugestii alternative profesionale:

1. **VANZA.ro** - Scurt, ușor de reținut, sugerează vânzare
2. **NEXO.ro** - Modern, sugerează conexiune între cumpărători și vânzători
3. **TROVA.ro** - De la "a găsi/trova", scurt și catchy
4. **SWAP.ro** - Pentru schimb și tranzacții
5. **PIAȚA.ro** - Direct la subiect, marketplace românesc
6. **AZIO.ro** - Sunet modern, profesional
7. **KARDO.ro** - Memorabil, unic, profesional
8. **MIXO.ro** - Sugerează varietate de anunțuri
9. **VENDO.ro** - Latin pentru "vând", internațional dar accesibil
10. **ZESTA.ro** - Sunet plăcut, modern, profesional

**Recomandare:** VANZA.ro, NEXO.ro sau TROVA.ro sunt cele mai potrivite pentru un marketplace general.

---

## ✨ Caracteristici

### Pentru Utilizatori
- ✅ Căutare avansată cu multiple filtre
- ✅ Salvare anunțuri favorite
- ✅ Sistem de mesagerie între utilizatori
- ✅ Istoric de căutări
- ✅ Căutări salvate cu notificări
- ✅ Gestionare completă cont utilizator
- ✅ Notificări în timp real

### Pentru Vânzători
- ✅ Publicare anunțuri cu fotografii multiple
- ✅ Editare și ștergere anunțuri
- ✅ Statistici vizualizări
- ✅ Promovare anunțuri
- ✅ Gestionare mesaje de la potențiali cumpărători
- ✅ Dashboard cu activitate recentă

### Design & UX
- ✅ Design modern și profesional
- ✅ Responsive (Mobile, Tablet, Desktop)
- ✅ Interfață intuitivă
- ✅ Animații subtile
- ✅ Culori vibrate și plăcute
- ✅ Iconuri Font Awesome

---

## 📁 Structura Proiectului

```
proiect/
│
├── index.html                  # Pagina principală
├── anunturi.html              # Listare anunțuri
├── anunt-detalii.html         # Detalii anunț individual
├── publica-anunt.html         # Formular publicare anunț
├── cautare.html               # Căutare avansată
├── contul-meu.html            # Dashboard utilizator
├── anunturi-mele.html         # Gestionare anunțuri proprii
├── favorite.html              # Anunțuri favorite
├── mesaje.html                # Sistem de mesagerie
├── setari.html                # Setări cont utilizator
│
├── styles.css                 # Stiluri CSS principale
├── script.js                  # Funcționalități JavaScript
│
└── README.md                  # Documentație (acest fișier)
```

---

## 🚀 Instalare și Rulare

### Metoda 1: Direct în Browser (Recomandată pentru început)

1. **Descarcă toate fișierele** într-un folder local
2. **Deschide `index.html`** în browser-ul tău preferat
3. **Gata!** Website-ul funcționează local

### Metoda 2: Cu Live Server (VS Code)

1. Instalează extensia **Live Server** în VS Code
2. Click dreapta pe `index.html`
3. Selectează **"Open with Live Server"**

### Metoda 3: Cu un server HTTP simplu

**Python:**
```bash
# Python 3
python -m http.server 8000

# Python 2
python -m SimpleHTTPServer 8000
```

**Node.js:**
```bash
npx http-server
```

Apoi accesează: `http://localhost:8000`

---

## 📄 Pagini Disponibile

### 1. **index.html** - Pagina Principală
- Hero section cu call-to-action
- Statistici platformă
- Categorii principale
- Anunțuri recente
- Footer complet

### 2. **anunturi.html** - Listare Anunțuri
- Grid cu toate anunțurile
- Filtre avansate (categorie, preț, locație)
- Sortare (dată, preț)
- Paginare
- Butoane favorite

### 3. **anunt-detalii.html** - Detalii Anunț
- Galerie de imagini
- Informații complete
- Caracteristici produsului
- Date vânzător
- Buton contact
- Sfaturi de siguranță
- Anunțuri similare

### 4. **publica-anunt.html** - Publicare Anunț
- Formular complet
- Upload fotografii (până la 10)
- Validare date
- Auto-save progres
- Previzualizare anunț

### 5. **cautare.html** - Căutare Avansată
- Căutare cu filtre multiple
- Căutări populare
- Istoric căutări
- Căutări salvate cu notificări

### 6. **contul-meu.html** - Dashboard Utilizator
- Statistici personale
- Activitate recentă
- Acțiuni rapide
- Link-uri către toate secțiunile

### 7. **anunturi-mele.html** - Gestionare Anunțuri
- Lista completă anunțuri proprii
- Filtrare după status (active, vândute, expirate)
- Acțiuni: editare, promovare, ștergere
- Statistici per anunț

### 8. **favorite.html** - Anunțuri Favorite
- Grid cu anunțurile salvate
- Acces rapid la detalii
- Eliminare din favorite

### 9. **mesaje.html** - Mesagerie
- Listă conversații
- Fereastră chat
- Mesaje în timp real
- Informații despre anunț

### 10. **setari.html** - Setări Cont
- Date personale
- Schimbare parolă
- Preferințe notificări
- Confidențialitate
- Ștergere cont

---

## 🎨 Personalizare

### Schimbarea Culorilor

Editează variabilele CSS în `styles.css`:

```css
:root {
    --primary-color: #FF8C42;      /* Culoarea principală (portocaliu) */
    --secondary-color: #4CAF50;    /* Culoarea secundară (verde) */
    --dark-bg: #2D3436;            /* Fundal întunecat (header) */
    --light-bg: #F5F5F5;           /* Fundal deschis */
}
```

### Schimbarea Logo-ului

În header-ul fiecărei pagini, găsește:

```html
<div class="logo">
    <i class="fas fa-circle-notch"></i>
    <span>Anunțuri & Oferte</span>
</div>
```

Înlocuiește cu:
- Logo image: `<img src="logo.png" alt="Logo">`
- Alt icon: Schimbă clasa Font Awesome
- Alt text: Schimbă textul din `<span>`

### Schimbarea Textelor

Toate textele sunt în română și pot fi personalizate direct în fișierele HTML.

---

## 🎭 Design și UX

### Paleta de Culori
- **Portocaliu (#FF8C42)**: Culoare principală, energică, atractivă
- **Verde (#4CAF50)**: Acțiuni pozitive, succes
- **Gri închis (#2D3436)**: Header, elemente de navigare
- **Alb (#FFFFFF)**: Fundal carduri, claritate

### Typography
- Font-family: System fonts (San Francisco, Segoe UI, Roboto)
- Responsive: dimensiuni adaptative pentru toate ecranele

### Responsive Breakpoints
- **Mobile**: < 480px
- **Tablet**: 481px - 768px
- **Desktop**: > 768px

---

## ⚙️ Funcționalități JavaScript

### `script.js` include:

1. **Sistem de Favorite**
   - Toggle favorite
   - Salvare în localStorage
   - Sincronizare între pagini

2. **Notificări**
   - Toast notifications
   - 4 tipuri: success, error, warning, info
   - Auto-dismiss după 3 secunde

3. **Căutare și Filtrare**
   - Filtre avansate
   - Salvare preferințe
   - Istoric căutări

4. **Validare Formulare**
   - Email validation
   - Phone validation
   - Required fields check

5. **Auto-save**
   - Salvare progres formulare
   - Previne pierderea datelor

6. **Scroll to Top**
   - Buton pentru scroll rapid
   - Apare după 300px scroll

7. **Tracking**
   - Page views
   - User interactions
   - Statistici locale

---

## 🔌 Integrare Backend

Website-ul este pregătit pentru integrare cu un backend. Iată punctele de integrare:

### API Endpoints Necesare

```javascript
// Autentificare
POST /api/auth/login
POST /api/auth/register
POST /api/auth/logout

// Anunțuri
GET /api/ads                    // Lista anunțuri
GET /api/ads/:id                // Detalii anunț
POST /api/ads                   // Creează anunț
PUT /api/ads/:id                // Actualizează anunț
DELETE /api/ads/:id             // Șterge anunț

// Utilizatori
GET /api/users/:id              // Profil utilizator
PUT /api/users/:id              // Actualizează profil

// Mesaje
GET /api/messages               // Lista conversații
GET /api/messages/:id           // Mesaje conversație
POST /api/messages              // Trimite mesaj

// Favorite
GET /api/favorites              // Lista favorite
POST /api/favorites/:adId       // Adaugă la favorite
DELETE /api/favorites/:adId     // Elimină din favorite

// Căutare
GET /api/search?q=query         // Căutare
POST /api/search/save           // Salvează căutare
```

### Tehnologii Backend Recomandate

- **Node.js + Express**: Rapid, popular
- **PHP + Laravel**: Robust, matur
- **Python + Django/Flask**: Versatil
- **Ruby on Rails**: Convention over configuration

### Baze de Date

- **PostgreSQL**: Recomandată pentru producție
- **MySQL**: Alternativă populară
- **MongoDB**: Pentru flexibilitate NoSQL

---

## 🌐 Browser Support

Website-ul funcționează pe:

- ✅ Chrome (versiunea 90+)
- ✅ Firefox (versiunea 88+)
- ✅ Safari (versiunea 14+)
- ✅ Edge (versiunea 90+)
- ✅ Opera (versiunea 76+)

### Mobile Browsers
- ✅ Chrome Mobile
- ✅ Safari iOS
- ✅ Samsung Internet
- ✅ Firefox Mobile

---

## 📱 Features Mobile

- Touch-friendly buttons
- Swipe gestures (unde este posibil)
- Optimizat pentru ecrane mici
- Menu hamburger
- Fast loading

---

## 🔐 Securitate

### Recomandări pentru Producție

1. **HTTPS obligatoriu**
2. **CSRF protection**
3. **XSS prevention**
4. **SQL injection prevention**
5. **Rate limiting**
6. **Password hashing** (bcrypt)
7. **JWT pentru autentificare**
8. **Validare server-side**

---

## 📈 Optimizări Viitoare

### Performance
- [ ] Lazy loading imagini
- [ ] CDN pentru assets
- [ ] Minificare CSS/JS
- [ ] Compression (gzip/brotli)
- [ ] Service Workers (PWA)

### Funcționalități
- [ ] Chat în timp real (WebSockets)
- [ ] Notificări push
- [ ] Plăți online
- [ ] Sistem rating/review
- [ ] Rapoarte detaliate
- [ ] Export date (PDF/Excel)
- [ ] Integrare social media

### SEO
- [ ] Meta tags optimizate
- [ ] Schema.org markup
- [ ] Sitemap.xml
- [ ] Robots.txt
- [ ] Open Graph tags
- [ ] Twitter Cards

---

## 🛠️ Debugging

### Console Logs
`script.js` include console.log pentru debugging. Pentru producție, elimină-le sau folosește:

```javascript
const DEBUG = false;
if (DEBUG) console.log('Debug message');
```

### Local Storage
Datele sunt salvate în localStorage. Pentru a reseta:

```javascript
localStorage.clear();
```

---

## 📞 Contact și Suport

Pentru întrebări, sugestii sau probleme:

- **Email**: contact@anunturi-oferte.ro (înlocuiește cu emailul tău)
- **Documentație**: Acest fișier README
- **Issues**: Creează un issue pe GitHub (dacă este cazul)

---

## 📝 Licență

Acest proiect este creat pentru uz personal/comercial. Ești liber să:
- ✅ Folosești codul pentru propriile proiecte
- ✅ Modifici și personalizezi după preferințe
- ✅ Folosești comercial
- ✅ Distribui copii

**Atribuire**: Apreciez dar nu este obligatorie.

---

## 🎉 Final Notes

**Mulțumiri pentru că folosești acest template!**

Acest website a fost construit cu atenție la detalii, având în vedere:
- User Experience (UX)
- Design modern
- Code quality
- Performance
- Accesibilitate

### Next Steps:

1. **Personalizează** culorile și textele
2. **Alege un nume de brand** din sugestiile oferite
3. **Cumpără domeniul** (ex: vanza.ro, nexo.ro)
4. **Integrează cu backend**
5. **Testează** pe device-uri reale
6. **Deploy** pe un hosting (Netlify, Vercel, AWS, etc.)

**Mult succes cu proiectul tău! 🚀**

---

## 📚 Resurse Utile

- [Font Awesome Icons](https://fontawesome.com/icons)
- [Google Fonts](https://fonts.google.com/)
- [Can I Use](https://caniuse.com/) - Browser compatibility
- [Placeholder Images](https://placeholder.com/)
- [Color Palette Generator](https://coolors.co/)

---

**Made with ❤️ in Romania**

*Versiune: 1.0.0*  
*Ultima actualizare: Noiembrie 2025*

