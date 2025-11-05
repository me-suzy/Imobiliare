# 🚀 Backend Node.js + Express + MongoDB

Backend complet pentru platforma de anunțuri, construit cu Node.js, Express și MongoDB.

---

## 📋 Caracteristici

✅ **Autentificare JWT** (Register, Login, Update Profile)  
✅ **CRUD Anunțuri** (Create, Read, Update, Delete)  
✅ **Upload Imagini** (până la 10 imagini per anunț)  
✅ **Sistem Mesagerie** (conversații și mesaje în timp real)  
✅ **Căutare și Filtrare** avansată  
✅ **Securitate** (Helmet, Rate Limiting, CORS)  
✅ **Validare Date** (express-validator)  

---

## 🛠️ Instalare

### Prerequisite:
- **Node.js** (v16 sau mai nou) - [Download](https://nodejs.org/)
- **MongoDB** (local sau Atlas) - [Download](https://www.mongodb.com/try/download/community)

### Pasul 1: Instalează dependențele

```bash
cd backend-nodejs
npm install
```

### Pasul 2: Configurare variabile de mediu

Creează un fișier `.env` în folder-ul `backend-nodejs/`:

```env
PORT=5000
NODE_ENV=development
MONGODB_URI=mongodb://localhost:27017/anunturi-db
JWT_SECRET=schimba-cu-un-secret-sigur-random-aici
FRONTEND_URL=http://localhost:3000
```

**IMPORTANT:** Schimbă `JWT_SECRET` cu un string random și sigur!

### Pasul 3: Creează folderul pentru uploads

```bash
mkdir uploads
mkdir uploads/ads
```

### Pasul 4: Pornește MongoDB

**Windows:**
```bash
mongod
```

**macOS/Linux:**
```bash
sudo systemctl start mongod
```

**Sau folosește MongoDB Atlas (cloud)** - [Tutorial](https://www.mongodb.com/cloud/atlas/register)

### Pasul 5: Pornește serverul

**Development mode (cu auto-restart):**
```bash
npm run dev
```

**Production mode:**
```bash
npm start
```

Serverul va rula pe **http://localhost:5000**

---

## 📡 API Endpoints

### **Autentificare**

#### Register
```http
POST /api/auth/register
Content-Type: application/json

{
  "name": "Ion Popescu",
  "email": "ion@example.com",
  "password": "parola123",
  "phone": "0722123456",
  "location": {
    "county": "bucuresti",
    "city": "București"
  }
}
```

#### Login
```http
POST /api/auth/login
Content-Type: application/json

{
  "email": "ion@example.com",
  "password": "parola123"
}
```

**Răspuns:**
```json
{
  "message": "Autentificare reușită!",
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "user": { ... }
}
```

#### Get Current User
```http
GET /api/auth/me
Authorization: Bearer YOUR_TOKEN_HERE
```

---

### **Anunțuri**

#### Get All Ads (cu filtre)
```http
GET /api/ads?page=1&limit=20&category=imobiliare&priceMin=1000&priceMax=100000
```

#### Get Single Ad
```http
GET /api/ads/:id
```

#### Create Ad
```http
POST /api/ads
Authorization: Bearer YOUR_TOKEN_HERE
Content-Type: multipart/form-data

{
  "title": "Apartament 3 camere",
  "description": "Descriere detaliată...",
  "category": "imobiliare",
  "price": 120000,
  "currency": "EUR",
  "condition": "nou",
  "location": {"county": "bucuresti", "city": "București"},
  "contact": {"name": "Ion", "phone": "0722123456"},
  "images": [file1, file2, ...]
}
```

#### Update Ad
```http
PUT /api/ads/:id
Authorization: Bearer YOUR_TOKEN_HERE
Content-Type: application/json

{
  "title": "Titlu actualizat",
  "price": 115000
}
```

#### Delete Ad
```http
DELETE /api/ads/:id
Authorization: Bearer YOUR_TOKEN_HERE
```

---

### **Mesaje**

#### Get Conversations
```http
GET /api/messages/conversations
Authorization: Bearer YOUR_TOKEN_HERE
```

#### Get Messages in Conversation
```http
GET /api/messages/:conversationId
Authorization: Bearer YOUR_TOKEN_HERE
```

#### Send Message
```http
POST /api/messages
Authorization: Bearer YOUR_TOKEN_HERE
Content-Type: application/json

{
  "receiverId": "64f1234567890abcdef12345",
  "adId": "64f1234567890abcdef67890",
  "content": "Bună, anunțul este disponibil?"
}
```

---

### **Utilizatori**

#### Get User Profile
```http
GET /api/users/:id
```

#### Get User's Ads
```http
GET /api/users/:id/ads
```

---

## 🔗 Conectare cu Frontend

În fișierele JavaScript din frontend, adaugă:

```javascript
const API_URL = 'http://localhost:5000/api';

// Example: Login
async function login(email, password) {
    const response = await fetch(`${API_URL}/auth/login`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ email, password })
    });
    
    const data = await response.json();
    
    if (data.token) {
        localStorage.setItem('token', data.token);
        localStorage.setItem('user', JSON.stringify(data.user));
    }
    
    return data;
}

// Example: Get Ads
async function getAds(filters = {}) {
    const queryString = new URLSearchParams(filters).toString();
    const response = await fetch(`${API_URL}/ads?${queryString}`);
    return await response.json();
}

// Example: Create Ad (cu autentificare)
async function createAd(formData) {
    const token = localStorage.getItem('token');
    
    const response = await fetch(`${API_URL}/ads`, {
        method: 'POST',
        headers: {
            'Authorization': `Bearer ${token}`
        },
        body: formData // FormData pentru upload imagini
    });
    
    return await response.json();
}
```

---

## 📁 Structura Proiectului

```
backend-nodejs/
├── models/
│   ├── User.js          # Model utilizator
│   ├── Ad.js            # Model anunț
│   └── Message.js       # Model mesaj + conversație
├── routes/
│   ├── auth.js          # Rute autentificare
│   ├── ads.js           # Rute anunțuri
│   ├── messages.js      # Rute mesaje
│   └── users.js         # Rute utilizatori
├── middleware/
│   └── auth.js          # Middleware autentificare JWT
├── uploads/
│   └── ads/             # Imagini uploadate
├── server.js            # Fișier principal server
├── package.json         # Dependențe npm
├── .env                 # Variabile de mediu (creează-l tu!)
└── README.md            # Acest fișier
```

---

## 🧪 Testare API cu Postman/Thunder Client

1. **Importă collection** sau testează manual
2. **Register** un utilizator nou
3. **Login** și copiază token-ul
4. **Adaugă token-ul** în header: `Authorization: Bearer YOUR_TOKEN`
5. **Testează** celelalte endpoint-uri

---

## 🔐 Securitate

✅ Parole hash-uite cu bcrypt  
✅ JWT pentru autentificare  
✅ Rate limiting (100 requests / 15 min)  
✅ Helmet pentru HTTP headers  
✅ CORS configurat  
✅ Validare input cu express-validator  

---

## 🚀 Deploy pe Producție

### **Opțiuni de Hosting:**

1. **Heroku** (gratuit pentru început)
2. **Railway** (modern, simplu)
3. **DigitalOcean** (VPS)
4. **AWS / Google Cloud**

### **MongoDB Hosting:**
- **MongoDB Atlas** (gratuit 512MB)

### **Pas deploy (Heroku example):**

```bash
# Login
heroku login

# Create app
heroku create nume-app-backend

# Add MongoDB
heroku addons:create mongolab:sandbox

# Deploy
git push heroku main

# Open
heroku open
```

---

## 📝 To-Do îmbunătățiri viitoare

- [ ] Email notifications (Nodemailer)
- [ ] WebSockets pentru mesaje real-time (Socket.io)
- [ ] Sistem de rating/review
- [ ] Payment integration (Stripe)
- [ ] Image optimization (Sharp)
- [ ] Caching (Redis)
- [ ] Tests (Jest/Mocha)

---

## 🐛 Troubleshooting

### MongoDB nu se conectează
- Verifică dacă MongoDB rulează: `mongod --version`
- Verifică MONGODB_URI în `.env`

### Eroare "Module not found"
```bash
npm install
```

### Port deja folosit
- Schimbă PORT în `.env`
- Sau oprește procesul: `killall node` (macOS/Linux)

---

## 📞 Suport

Dacă întâmpini probleme, verifică:
- Node.js version: `node --version` (trebuie >= 16)
- MongoDB version: `mongod --version`
- Toate dependențele instalate: `npm list`

---

**Backend-ul este complet funcțional și gata de folosit! 🎉**

**Next:** Integrează cu frontend-ul tău HTML/CSS/JS!


