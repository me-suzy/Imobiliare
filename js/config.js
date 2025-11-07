// Configurare automată pentru LOCAL vs LIVE
// Acest fișier detectează automat unde rulează aplicația

// Detectează dacă rulăm local sau pe server
const isLocal = window.location.hostname === 'localhost' || 
                window.location.hostname === '127.0.0.1' ||
                window.location.hostname === '';

// Configurare API URL
const API_CONFIG = {
    // URL-uri API
    BASE_URL: isLocal ? 'http://localhost/api/' : 'https://marc.ro/api/',
    
    // Endpoints
    AUTH: 'auth.php',
    ANUNTURI: 'anunturi.php',
    UPLOAD: 'upload.php',
    
    // Configurare site
    SITE_URL: isLocal ? 'http://localhost/' : 'https://marc.ro/',
    SITE_NAME: 'Marc.ro',
    SITE_DESCRIPTION: 'Portal de Anunțuri Gratuite',
    
    // Upload
    MAX_FILE_SIZE: 5 * 1024 * 1024, // 5MB
    ALLOWED_IMAGES: ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'],
    MAX_IMAGES: 10,
    
    // Paginare
    ANUNTURI_PER_PAGINA: 20,
    
    // Debug mode (doar local)
    DEBUG: isLocal
};

// Funcții helper pentru API calls
const API = {
    // GET request
    async get(endpoint, params = {}) {
        const url = new URL(API_CONFIG.BASE_URL + endpoint);
        Object.keys(params).forEach(key => url.searchParams.append(key, params[key]));
        
        try {
            const response = await fetch(url, {
                credentials: 'include'
            });
            
            if (API_CONFIG.DEBUG) {
                console.log(`GET ${url}`, response);
            }
            
            return await response.json();
        } catch (error) {
            console.error('API GET Error:', error);
            throw error;
        }
    },
    
    // POST request
    async post(endpoint, data = {}) {
        try {
            const response = await fetch(API_CONFIG.BASE_URL + endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data),
                credentials: 'include'
            });
            
            if (API_CONFIG.DEBUG) {
                console.log(`POST ${endpoint}`, data, response);
            }
            
            return await response.json();
        } catch (error) {
            console.error('API POST Error:', error);
            throw error;
        }
    },
    
    // PUT request
    async put(endpoint, data = {}) {
        try {
            const response = await fetch(API_CONFIG.BASE_URL + endpoint, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data),
                credentials: 'include'
            });
            
            if (API_CONFIG.DEBUG) {
                console.log(`PUT ${endpoint}`, data, response);
            }
            
            return await response.json();
        } catch (error) {
            console.error('API PUT Error:', error);
            throw error;
        }
    },
    
    // DELETE request
    async delete(endpoint, params = {}) {
        const url = new URL(API_CONFIG.BASE_URL + endpoint);
        Object.keys(params).forEach(key => url.searchParams.append(key, params[key]));
        
        try {
            const response = await fetch(url, {
                method: 'DELETE',
                credentials: 'include'
            });
            
            if (API_CONFIG.DEBUG) {
                console.log(`DELETE ${url}`, response);
            }
            
            return await response.json();
        } catch (error) {
            console.error('API DELETE Error:', error);
            throw error;
        }
    },
    
    // Upload fișiere (multipart/form-data)
    async uploadFiles(files) {
        const formData = new FormData();
        
        // Adaugă fiecare fișier
        for (let i = 0; i < files.length; i++) {
            formData.append('imagini[]', files[i]);
        }
        
        try {
            const response = await fetch(API_CONFIG.BASE_URL + API_CONFIG.UPLOAD, {
                method: 'POST',
                body: formData,
                credentials: 'include'
            });
            
            if (API_CONFIG.DEBUG) {
                console.log('Upload Files', files, response);
            }
            
            return await response.json();
        } catch (error) {
            console.error('API Upload Error:', error);
            throw error;
        }
    }
};

// Funcții de autentificare
const Auth = {
    // Verifică dacă utilizatorul e autentificat
    async check() {
        return await API.get(API_CONFIG.AUTH, { action: 'check' });
    },
    
    // Înregistrare
    async register(nume, email, parola, telefon) {
        return await API.post(API_CONFIG.AUTH, {
            action: 'register',
            nume,
            email,
            parola,
            telefon
        });
    },
    
    // Login
    async login(email, parola) {
        return await API.post(API_CONFIG.AUTH, {
            action: 'login',
            email,
            parola
        });
    },
    
    // Logout
    async logout() {
        return await API.post(API_CONFIG.AUTH, {
            action: 'logout'
        });
    },
    
    // Preia utilizatorul curent din sesiune
    currentUser: null,
    
    // Inițializează autentificarea
    async init() {
        const result = await this.check();
        if (result.autentificat) {
            this.currentUser = result.user;
        }
        return result.autentificat;
    }
};

// Funcții pentru anunțuri
const Anunturi = {
    // Preia listă anunțuri cu filtre
    async getAll(filters = {}) {
        return await API.get(API_CONFIG.ANUNTURI, filters);
    },
    
    // Preia un anunț specific
    async get(id) {
        return await API.get(API_CONFIG.ANUNTURI, { id });
    },
    
    // Creează anunț nou
    async create(anuntData) {
        return await API.post(API_CONFIG.ANUNTURI, anuntData);
    },
    
    // Actualizează anunț
    async update(id, updates) {
        return await API.put(API_CONFIG.ANUNTURI, { id, ...updates });
    },
    
    // Șterge anunț
    async delete(id) {
        return await API.delete(API_CONFIG.ANUNTURI, { id });
    }
};

// Elimină diacriticele dintr-un șir (normalizare Unicode)
// Funcție globală pentru căutare fără diacritice
// Funcționează pentru toate limbile, inclusiv română (ă, â, î, ș, ț)
function removeDiacritics(str) {
    if (!str) return '';
    // Normalizează șirul în formă decompozitată (NFD) și elimină diacriticele
    // Unicode range \u0300-\u036f = toate diacriticele (accenturi, brevia, etc.)
    return str.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
}

// Utilități
const Utils = {
    // Formatare preț
    formatPrice(price, currency = 'RON') {
        return new Intl.NumberFormat('ro-RO', {
            style: 'currency',
            currency: currency
        }).format(price);
    },
    
    // Formatare dată
    formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('ro-RO', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    },
    
    // Formatare dată relativă (acum 2 ore, ieri, etc.)
    formatRelativeDate(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diff = now - date;
        
        const seconds = Math.floor(diff / 1000);
        const minutes = Math.floor(seconds / 60);
        const hours = Math.floor(minutes / 60);
        const days = Math.floor(hours / 24);
        
        if (seconds < 60) return 'Acum';
        if (minutes < 60) return `Acum ${minutes} ${minutes === 1 ? 'minut' : 'minute'}`;
        if (hours < 24) return `Acum ${hours} ${hours === 1 ? 'oră' : 'ore'}`;
        if (days < 7) return `Acum ${days} ${days === 1 ? 'zi' : 'zile'}`;
        
        return this.formatDate(dateString);
    },
    
    // Validare email
    isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    },
    
    // Validare telefon
    isValidPhone(phone) {
        return /^[0-9]{10}$/.test(phone.replace(/\s/g, ''));
    },
    
    // Afișare notificare
    showNotification(message, type = 'info') {
        // Adaugă animațiile CSS dacă nu există deja
        if (!document.getElementById('notification-animations')) {
            const style = document.createElement('style');
            style.id = 'notification-animations';
            style.textContent = `
                @keyframes slideInRight {
                    from {
                        transform: translateX(400px);
                        opacity: 0;
                    }
                    to {
                        transform: translateX(0);
                        opacity: 1;
                    }
                }
                @keyframes slideOutRight {
                    from {
                        transform: translateX(0);
                        opacity: 1;
                    }
                    to {
                        transform: translateX(400px);
                        opacity: 0;
                    }
                }
            `;
            document.head.appendChild(style);
        }
        
        // Implementare toast notification avansată
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        const bgColor = type === 'success' ? '#4CAF50' : type === 'error' ? '#f44336' : type === 'warning' ? '#FF9800' : '#2196F3';
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${bgColor};
            color: white;
            padding: 15px 20px;
            border-radius: 5px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            z-index: 10000;
            animation: slideInRight 0.3s ease-out;
            max-width: 400px;
            word-wrap: break-word;
        `;
        notification.textContent = message;
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.animation = 'slideOutRight 0.3s ease-in';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    },
    
    // Redirect
    redirect(url) {
        window.location.href = url;
    }
};

// Log configurație la încărcare (doar în modul debug)
if (API_CONFIG.DEBUG) {
    console.log('🔧 API Configuration:', API_CONFIG);
    console.log('📍 Running on:', isLocal ? 'LOCAL (XAMPP)' : 'LIVE (marc.ro)');
}

// Export pentru utilizare în alte scripturi
window.API_CONFIG = API_CONFIG;
window.API = API;
window.Auth = Auth;
window.Anunturi = Anunturi;
window.Utils = Utils;
window.removeDiacritics = removeDiacritics;

