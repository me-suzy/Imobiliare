// Funcție GLOBALĂ pentru inițializarea dropdown-ului (trebuie să fie disponibilă oricând)
function initDropdown() {
    const dropdown = document.querySelector('.dropdown');
    const userMenuBtn = document.querySelector('.user-menu');
    
    if (!userMenuBtn || !dropdown) {
        // Dacă nu există elementele, nu face nimic
        return;
    }
    
    // Șterge event listeners existente - clonează butonul
    const newBtn = userMenuBtn.cloneNode(true);
    userMenuBtn.parentNode.replaceChild(newBtn, userMenuBtn);
    
    // Adaugă event listener nou pentru click
    newBtn.addEventListener('click', function(event) {
        event.preventDefault();
        event.stopPropagation();
        
        const dropdown = document.querySelector('.dropdown');
        if (dropdown) {
            const wasActive = dropdown.classList.contains('active');
            
            // Șterge active de la toate celelalte dropdown-uri
            document.querySelectorAll('.dropdown.active').forEach(d => {
                d.classList.remove('active');
            });
            
            // Toggle dropdown-ul curent
            if (wasActive) {
                dropdown.classList.remove('active');
            } else {
                dropdown.classList.add('active');
            }
            
            console.log('Dropdown:', wasActive ? 'CLOSED' : 'OPENED');
        }
        
        // Oprește propagarea pentru a preveni închiderea imediată
        return false;
    });
}

// Face funcția disponibilă global
window.initDropdown = initDropdown;

// Mobile menu toggle
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const navMenu = document.querySelector('.nav-menu');
    
    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', function() {
            navMenu.classList.toggle('active');
        });
    }

    // Inițializează dropdown-ul imediat
    initDropdown();
    
    // Reinițializează după un mic delay pentru a fi sigur că funcționează pe toate paginile
    setTimeout(initDropdown, 300);
    
    // Reinițializează și după un delay mai mare (pentru pagini care se încarcă lent sau care modifică dropdown-ul dinamic)
    setTimeout(initDropdown, 800);

    // Close dropdown when clicking outside
    // Folosește setTimeout pentru a permite toggle-ului să se execute primul
    document.addEventListener('click', function(event) {
        setTimeout(function() {
            const clickedElement = event.target;
            const dropdown = clickedElement.closest('.dropdown');
            const isUserMenu = clickedElement.closest('.user-menu');
            const isDropdownContent = clickedElement.closest('.dropdown-content');
            
            // Dacă click-ul NU e pe butonul user-menu și NU e în dropdown-content
            if (!isUserMenu && !isDropdownContent) {
                // Închide toate dropdown-urile active
                document.querySelectorAll('.dropdown.active').forEach(d => {
                    d.classList.remove('active');
                });
            }
        }, 10); // Mic delay pentru a permite toggle-ului să se execute
        
        // Close mobile menu
        if (!event.target.closest('.navbar')) {
            if (navMenu) navMenu.classList.remove('active');
        }
    });

    // Initialize favorites from localStorage
    initializeFavorites();
});

// Favorite functionality
function toggleFavorite(element) {
    const icon = element.querySelector('i');
    
    if (icon.classList.contains('far')) {
        icon.classList.remove('far');
        icon.classList.add('fas');
        element.classList.add('active');
        showNotification('Anunț adăugat la favorite!', 'success');
    } else {
        icon.classList.remove('fas');
        icon.classList.add('far');
        element.classList.remove('active');
        showNotification('Anunț eliminat din favorite!', 'info');
    }
    
    // Save to localStorage
    saveFavorites();
}

function initializeFavorites() {
    const favorites = JSON.parse(localStorage.getItem('favorites') || '[]');
    // Initialize favorite buttons based on saved data
}

function saveFavorites() {
    const favoriteButtons = document.querySelectorAll('.favorite-btn.active');
    const favorites = [];
    favoriteButtons.forEach(btn => {
        // Extract ad ID and save
        favorites.push({ id: Math.random() });
    });
    localStorage.setItem('favorites', JSON.stringify(favorites));
}

// Search functionality
function searchAds() {
    const keyword = document.getElementById('searchKeyword')?.value;
    const location = document.getElementById('searchLocation')?.value;
    const category = document.getElementById('filterCategory')?.value;
    
    let queryParams = [];
    if (keyword) queryParams.push(`search=${encodeURIComponent(keyword)}`);
    if (location) queryParams.push(`location=${encodeURIComponent(location)}`);
    if (category) queryParams.push(`category=${encodeURIComponent(category)}`);
    
    window.location.href = `anunturi.html?${queryParams.join('&')}`;
}

// User logout
// Funcție logout reală prin API
async function logout() {
    if (confirm('Sigur vrei să te deconectezi?')) {
        try {
            await API.post('auth.php', { action: 'logout' });
            Utils.showNotification('Te-ai deconectat cu succes!', 'success');
            // Șterge datele din localStorage
            localStorage.removeItem('user');
            // Actualizează header-ul imediat
            if (typeof updateHeaderAuth === 'function') {
                await updateHeaderAuth();
            }
            // Redirect la index după 1 secundă
            setTimeout(() => {
                window.location.href = 'index.html';
            }, 1000);
        } catch (error) {
            console.error('Eroare la logout:', error);
            // Chiar dacă e eroare, șterge datele locale și redirect
            localStorage.removeItem('user');
            // Actualizează header-ul imediat
            if (typeof updateHeaderAuth === 'function') {
                await updateHeaderAuth();
            }
            Utils.showNotification('Te-ai deconectat!', 'info');
            setTimeout(() => {
                window.location.href = 'index.html';
            }, 1000);
        }
    }
}

// Funcția updateHeaderAuth este în js/header-auth.js
// Nu mai este necesară aici pentru a evita conflictele

// Notification system
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div style="display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-${getIconForType(type)}"></i>
            <span>${message}</span>
        </div>
    `;
    
    notification.style.cssText = `
        position: fixed;
        top: 80px;
        right: 20px;
        background: ${getColorForType(type)};
        color: white;
        padding: 15px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 10000;
        animation: slideInRight 0.3s ease;
        max-width: 350px;
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

function getIconForType(type) {
    const icons = {
        success: 'check-circle',
        error: 'exclamation-circle',
        warning: 'exclamation-triangle',
        info: 'info-circle'
    };
    return icons[type] || 'info-circle';
}

function getColorForType(type) {
    const colors = {
        success: '#4CAF50',
        error: '#F44336',
        warning: '#FF9800',
        info: '#2196F3'
    };
    return colors[type] || '#2196F3';
}

// Add animation styles
const style = document.createElement('style');
style.innerHTML = `
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

// Form validation
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function validatePhone(phone) {
    const re = /^[0-9]{10}$/;
    return re.test(phone.replace(/\s/g, ''));
}

// Image preview functionality
function previewImages(input) {
    if (input.files) {
        const files = Array.from(input.files);
        const maxFiles = 10;
        const maxSize = 5 * 1024 * 1024; // 5MB
        
        files.slice(0, maxFiles).forEach((file, index) => {
            if (file.size > maxSize) {
                showNotification(`Fișierul ${file.name} este prea mare (max 5MB)`, 'error');
                return;
            }
            
            if (!file.type.startsWith('image/')) {
                showNotification(`Fișierul ${file.name} nu este o imagine validă`, 'error');
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                // Create preview element
                console.log('Image loaded:', file.name);
            };
            reader.readAsDataURL(file);
        });
    }
}

// Price formatting
function formatPrice(price, currency = 'RON') {
    const formatter = new Intl.NumberFormat('ro-RO', {
        style: 'currency',
        currency: currency,
        minimumFractionDigits: 0,
        maximumFractionDigits: 2
    });
    return formatter.format(price);
}

// Date formatting
function formatDate(date) {
    const now = new Date();
    const diff = now - new Date(date);
    const seconds = Math.floor(diff / 1000);
    const minutes = Math.floor(seconds / 60);
    const hours = Math.floor(minutes / 60);
    const days = Math.floor(hours / 24);
    
    if (seconds < 60) return 'Chiar acum';
    if (minutes < 60) return `Acum ${minutes} ${minutes === 1 ? 'minut' : 'minute'}`;
    if (hours < 24) return `Acum ${hours} ${hours === 1 ? 'oră' : 'ore'}`;
    if (days === 1) return 'Ieri';
    if (days < 7) return `Acum ${days} zile`;
    
    return new Date(date).toLocaleDateString('ro-RO');
}

// Share functionality
function shareAd(title, url) {
    if (navigator.share) {
        navigator.share({
            title: title,
            url: url
        }).then(() => {
            showNotification('Anunț distribuit cu succes!', 'success');
        }).catch(err => {
            console.log('Error sharing:', err);
        });
    } else {
        // Fallback: copy to clipboard
        navigator.clipboard.writeText(url).then(() => {
            showNotification('Link copiat în clipboard!', 'success');
        });
    }
}

// Report ad functionality
function reportAd(adId) {
    const reasons = [
        'Spam sau publicitate înșelătoare',
        'Conținut ilegal sau ofensator',
        'Produs fals sau înșelător',
        'Preț incorect sau înșelător',
        'Imagini irelevante',
        'Alt motiv'
    ];
    
    let reasonsHTML = reasons.map((reason, index) => 
        `<label style="display: block; margin: 10px 0;">
            <input type="radio" name="reportReason" value="${index}">
            ${reason}
        </label>`
    ).join('');
    
    const modal = document.createElement('div');
    modal.className = 'modal';
    modal.style.display = 'block';
    modal.innerHTML = `
        <div class="modal-content">
            <div class="modal-header">
                <h3>Raportează anunțul</h3>
                <span class="close" onclick="this.closest('.modal').remove()">&times;</span>
            </div>
            <div>
                <p>Te rugăm să selectezi motivul pentru care raportezi acest anunț:</p>
                ${reasonsHTML}
                <textarea class="form-control" placeholder="Detalii suplimentare (opțional)" style="margin-top: 15px;"></textarea>
                <div style="margin-top: 20px; display: flex; gap: 10px; justify-content: flex-end;">
                    <button class="btn btn-outline" onclick="this.closest('.modal').remove()">Anulează</button>
                    <button class="btn btn-danger" onclick="submitReport(${adId})">Trimite raportul</button>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
}

function submitReport(adId) {
    showNotification('Raportul tău a fost trimis. Mulțumim!', 'success');
    document.querySelector('.modal').remove();
}

// Contact seller
async function contactSeller(sellerId, adId) {
    try {
        const authStatus = await Auth.check();
        if (!authStatus.autentificat) {
            showNotification('Trebuie să fii autentificat pentru a contacta vânzătorul', 'warning');
            setTimeout(() => {
                window.location.href = `login.html?return=${encodeURIComponent(`mesaje.html?seller=${sellerId}&ad=${adId}`)}`;
            }, 1500);
            return;
        }

        let targetUrl = `mesaje.html?seller=${sellerId}`;
        if (adId) {
            targetUrl += `&ad=${adId}`;
        }
        window.location.href = targetUrl;
    } catch (error) {
        console.error('Eroare la verificarea autentificării:', error);
        showNotification('Nu am putut verifica autentificarea. Încearcă din nou.', 'error');
    }
}

// Filter and sort functionality
function applyFilters() {
    const filters = {
        category: document.getElementById('filterCategory')?.value,
        priceMin: document.getElementById('priceMin')?.value,
        priceMax: document.getElementById('priceMax')?.value,
        sortBy: document.getElementById('sortBy')?.value
    };
    
    // Save filters to localStorage
    localStorage.setItem('searchFilters', JSON.stringify(filters));
    
    // Apply filters (in real app, this would filter the results)
    showNotification('Filtrele au fost aplicate!', 'success');
}

function resetFilters() {
    localStorage.removeItem('searchFilters');
    
    // Reset form fields
    const fields = ['filterCategory', 'priceMin', 'priceMax', 'sortBy', 'searchKeyword', 'searchLocation'];
    fields.forEach(field => {
        const element = document.getElementById(field);
        if (element) {
            if (element.tagName === 'SELECT') {
                element.selectedIndex = 0;
            } else {
                element.value = '';
            }
        }
    });
    
    showNotification('Filtrele au fost resetate!', 'info');
}

// Load saved filters
function loadSavedFilters() {
    const savedFilters = JSON.parse(localStorage.getItem('searchFilters') || '{}');
    
    Object.keys(savedFilters).forEach(key => {
        const element = document.getElementById(key);
        if (element && savedFilters[key]) {
            element.value = savedFilters[key];
        }
    });
}

// Scroll to top button
window.addEventListener('scroll', function() {
    const scrollBtn = document.getElementById('scrollToTop');
    if (scrollBtn) {
        if (window.pageYOffset > 300) {
            scrollBtn.style.display = 'block';
        } else {
            scrollBtn.style.display = 'none';
        }
    }
});

function scrollToTop() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

// Add scroll to top button
const scrollButton = document.createElement('button');
scrollButton.id = 'scrollToTop';
scrollButton.innerHTML = '<i class="fas fa-arrow-up"></i>';
scrollButton.onclick = scrollToTop;
scrollButton.style.cssText = `
    display: none;
    position: fixed;
    bottom: 30px;
    right: 30px;
    z-index: 99;
    border: none;
    outline: none;
    background-color: var(--primary-color);
    color: white;
    cursor: pointer;
    padding: 15px;
    border-radius: 50%;
    font-size: 18px;
    width: 50px;
    height: 50px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    transition: all 0.3s;
`;
scrollButton.onmouseover = function() {
    this.style.backgroundColor = '#FF6B35';
    this.style.transform = 'scale(1.1)';
};
scrollButton.onmouseout = function() {
    this.style.backgroundColor = '#FF8C42';
    this.style.transform = 'scale(1)';
};
document.body.appendChild(scrollButton);

// Auto-save form data (for publish ad page)
function autoSaveForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return;
    
    const inputs = form.querySelectorAll('input, textarea, select');
    inputs.forEach(input => {
        input.addEventListener('change', function() {
            const formData = new FormData(form);
            const data = {};
            formData.forEach((value, key) => {
                data[key] = value;
            });
            localStorage.setItem(`formDraft_${formId}`, JSON.stringify(data));
            showNotification('Progres salvat automat', 'info');
        });
    });
}

// Load form draft
function loadFormDraft(formId) {
    const draft = localStorage.getItem(`formDraft_${formId}`);
    if (draft) {
        const data = JSON.parse(draft);
        Object.keys(data).forEach(key => {
            const input = document.querySelector(`[name="${key}"]`);
            if (input) input.value = data[key];
        });
    }
}

// Clear form draft after successful submission
function clearFormDraft(formId) {
    localStorage.removeItem(`formDraft_${formId}`);
}

// Statistics tracking
function trackPageView(pageName) {
    const views = JSON.parse(localStorage.getItem('pageViews') || '{}');
    views[pageName] = (views[pageName] || 0) + 1;
    localStorage.setItem('pageViews', JSON.stringify(views));
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Track page view
    trackPageView(document.title);
    
    // Load saved filters if on search/listing page
    if (window.location.pathname.includes('anunturi') || window.location.pathname.includes('cautare')) {
        loadSavedFilters();
    }
    
    // Enable auto-save for publish form
    if (document.getElementById('publishForm')) {
        autoSaveForm('publishForm');
        loadFormDraft('publishForm');
    }
});

// Export functions for use in HTML
window.toggleFavorite = toggleFavorite;
window.searchAds = searchAds;
window.logout = logout;
window.shareAd = shareAd;
window.reportAd = reportAd;
window.contactSeller = contactSeller;
window.applyFilters = applyFilters;
window.resetFilters = resetFilters;
window.scrollToTop = scrollToTop;
// Utils, Auth, API sunt deja definite în config.js
// Nu mai declarăm duplicate pentru a evita erorile "has already been declared"

// Error display function
function showError(message) {
    if (window.Utils && window.Utils.showNotification) {
        window.Utils.showNotification(message, 'error');
    } else {
        alert('Eroare: ' + message);
    }
}

// Export
window.showError = showError;

console.log('Script.js loaded successfully!');

