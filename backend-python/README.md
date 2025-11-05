# 🐍 Backend Python + Flask + SQLite

Backend complet pentru platforma de anunțuri, construit cu Python, Flask și SQLite.

---

## 📋 Caracteristici

✅ **Flask REST API**  
✅ **SQLAlchemy ORM**  
✅ **JWT Autentificare**  
✅ **SQLite Database** (usor de configurat)  
✅ **Bcrypt pentru parole**  
✅ **CORS enabled**  

---

## 🛠️ Instalare

### Prerequisite:
- **Python** (3.8 sau mai nou) - [Download](https://www.python.org/downloads/)
- **pip** (vine cu Python)

### Pasul 1: Creează virtual environment

```bash
cd backend-python
python -m venv venv
```

**Activează environment:**

Windows:
```bash
venv\Scripts\activate
```

macOS/Linux:
```bash
source venv/bin/activate
```

### Pasul 2: Instalează dependențe

```bash
pip install -r requirements.txt
```

### Pasul 3: Pornește serverul

```bash
python app.py
```

Serverul va rula pe **http://localhost:5000**

---

## 📡 API Endpoints

Exact ca la Node.js și PHP! API-ul este identic.

### **Autentificare**

```http
POST /api/auth/register
POST /api/auth/login
GET /api/auth/me (cu JWT)
```

### **Anunțuri**

```http
GET /api/ads
GET /api/ads/:id
POST /api/ads (cu JWT)
DELETE /api/ads/:id (cu JWT)
```

Exemplu request:
```python
import requests

# Register
response = requests.post('http://localhost:5000/api/auth/register', json={
    'name': 'Ion Popescu',
    'email': 'ion@example.com',
    'password': 'parola123',
    'phone': '0722123456'
})

print(response.json())

# Login
response = requests.post('http://localhost:5000/api/auth/login', json={
    'email': 'ion@example.com',
    'password': 'parola123'
})

token = response.json()['token']

# Get Ads cu autentificare
headers = {'Authorization': f'Bearer {token}'}
response = requests.get('http://localhost:5000/api/ads', headers=headers)
```

---

## 🔗 Conectare cu Frontend

Identic cu Node.js și PHP! JavaScript-ul este același:

```javascript
const API_URL = 'http://localhost:5000/api';

async function login(email, password) {
    const response = await fetch(`${API_URL}/auth/login`, {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ email, password })
    });
    
    const data = await response.json();
    localStorage.setItem('token', data.token);
    return data;
}
```

---

## 📁 Structura Proiectului

```
backend-python/
├── app.py              # Aplicația principală Flask
├── requirements.txt    # Dependențe Python
├── anunturi.db         # Database SQLite (se creează automat)
└── README.md
```

---

## 🚀 Deploy pe Producție

### **Opțiuni Hosting:**

1. **Heroku**
2. **PythonAnywhere** (excelent pentru Flask)
3. **Railway**
4. **Google Cloud Run**

### **Deploy pe Heroku:**

1. Creează `Procfile`:
```
web: gunicorn app:app
```

2. Adaugă gunicorn în `requirements.txt`:
```
gunicorn==21.2.0
```

3. Deploy:
```bash
heroku create nume-app
git push heroku main
```

---

## 🔐 Securitate

**IMPORTANT:**

1. **Schimbă** `JWT_SECRET_KEY` în `app.py`:
```python
app.config['JWT_SECRET_KEY'] = 'genereaza-un-secret-foarte-lung-si-aleator-aici'
```

2. **Pentru producție**, folosește PostgreSQL în loc de SQLite:
```python
app.config['SQLALCHEMY_DATABASE_URI'] = 'postgresql://user:pass@localhost/dbname'
```

3. **Dezactivează** debug mode:
```python
app.run(debug=False)
```

---

## 🐛 Troubleshooting

### Eroare la instalare dependențe
```bash
pip install --upgrade pip
pip install -r requirements.txt --no-cache-dir
```

### Port deja folosit
Schimbă portul în `app.py`:
```python
app.run(debug=True, port=5001)
```

### Import error
Asigură-te că virtual environment-ul e activat:
```bash
venv\Scripts\activate  # Windows
source venv/bin/activate  # macOS/Linux
```

---

## 📝 Avantaje Python/Flask

✅ **Cod curat și ușor de citit**  
✅ **Rapid de dezvoltat**  
✅ **Multe librării disponibile**  
✅ **Perfect pentru ML/AI features viitoare**  
✅ **SQLite = zero configurare DB**  

---

## 🔄 Migrare la PostgreSQL (recomandat pentru producție)

1. Instalează psycopg2:
```bash
pip install psycopg2-binary
```

2. Schimbă connection string:
```python
app.config['SQLALCHEMY_DATABASE_URI'] = 'postgresql://user:pass@localhost/anunturi_db'
```

3. Rulează din nou:
```bash
python app.py
```

---

**Backend Python este gata de folosit! 🐍**

**Simplu, rapid, elegant!** 🎉


