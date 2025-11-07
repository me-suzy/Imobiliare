// Script pentru actualizarea header-ului în funcție de autentificare
// Se include în toate paginile

// Funcție pentru actualizarea header-ului
async function updateHeaderAuth() {
    try {
        const authCheck = await Auth.check();
        const dropdownContent = document.querySelector('.dropdown-content');
        
        if (!dropdownContent) {
            // Dacă nu există dropdown-content, reinițializează dropdown-ul
            if (typeof window.initDropdown === 'function') {
                setTimeout(window.initDropdown, 100);
            } else if (typeof initDropdown === 'function') {
                setTimeout(initDropdown, 100);
            }
            return;
        }
        
        // Obține toate link-urile
        const allLinks = Array.from(dropdownContent.querySelectorAll('a'));
        
        if (authCheck && authCheck.autentificat) {
            // Utilizator AUTENTIFICAT - afișează meniul complet
            // IMPORTANT: Păstrează tipul de cont din sesiune (admin rămâne admin!)
            
            // Afișează toate link-urile normale
            allLinks.forEach(link => {
                const text = link.textContent.trim();
                if (text === 'Contul Meu' ||
                    text === 'Anunțurile Mele' ||
                    text === 'Mesaje' ||
                    text === 'Plăți' ||
                    text === 'Ratinguri' ||
                    text === 'Setări' ||
                    text === 'Deconectare' ||
                    link.id === 'admin-link') {
                    link.style.display = 'block';
                } else if (text.includes('Conectare') || 
                          (link.href && link.href.includes('login.html'))) {
                    link.style.display = 'none';
                }
            });
            
            // Actualizează link-ul de deconectare
            let logoutLink = allLinks.find(link => 
                link.textContent.trim() === 'Deconectare' || 
                (link.onclick && link.onclick.toString().includes('logout'))
            );
            
            if (logoutLink) {
                logoutLink.style.display = 'block';
                logoutLink.textContent = 'Deconectare';
                logoutLink.onclick = function(e) { 
                    e.preventDefault(); 
                    if (typeof window.logout === 'function') {
                        window.logout(); 
                    }
                };
            } else {
                // Creează link-ul de deconectare
                logoutLink = document.createElement('a');
                logoutLink.href = '#';
                logoutLink.textContent = 'Deconectare';
                logoutLink.onclick = function(e) { 
                    e.preventDefault(); 
                    if (typeof window.logout === 'function') {
                        window.logout(); 
                    }
                };
                dropdownContent.appendChild(logoutLink);
            }
            
            // Admin link
            const adminLink = dropdownContent.querySelector('#admin-link');
            if (adminLink) {
                if (authCheck.user && authCheck.user.tip_cont === 'admin') {
                    adminLink.style.display = 'block';
                } else {
                    adminLink.style.display = 'none';
                }
            }
            
            // Admin Parole link (pentru admin.html)
            const adminParoleLink = dropdownContent.querySelector('#admin-parole-link');
            if (adminParoleLink) {
                if (authCheck.user && authCheck.user.tip_cont === 'admin') {
                    adminParoleLink.style.display = 'block';
                } else {
                    adminParoleLink.style.display = 'none';
                }
            }
            
        } else {
            // Utilizator NEautentificat - afișează DOAR "Conectare"
            
            // Ascunde toate link-urile
            allLinks.forEach(link => {
                if (link.id !== 'admin-link') {
                    link.style.display = 'none';
                }
            });
            
            // Șterge link-ul vechi de login dacă există
            const oldLoginLinks = dropdownContent.querySelectorAll('a[href*="login"]');
            oldLoginLinks.forEach(link => link.remove());
            
            // Creează link-ul nou de conectare
            const loginLink = document.createElement('a');
            loginLink.href = 'login.html?return=' + encodeURIComponent(window.location.href);
            loginLink.innerHTML = '<i class="fas fa-sign-in-alt"></i> Conectare';
            dropdownContent.insertBefore(loginLink, dropdownContent.firstChild);
            loginLink.style.display = 'block';
            
            // Ascunde link-ul admin
            const adminLink = dropdownContent.querySelector('#admin-link');
            if (adminLink) {
                adminLink.style.display = 'none';
            }
        }
    } catch (error) {
        console.error('Eroare la actualizarea header-ului:', error);
        // Dacă e eroare, presupun că nu e autentificat
        const dropdownContent = document.querySelector('.dropdown-content');
        if (dropdownContent) {
            const allLinks = Array.from(dropdownContent.querySelectorAll('a'));
            allLinks.forEach(link => {
                if (link.id !== 'admin-link') {
                    link.style.display = 'none';
                }
            });
            
            // Creează link de conectare
            const loginLink = document.createElement('a');
            loginLink.href = 'login.html?return=' + encodeURIComponent(window.location.href);
            loginLink.innerHTML = '<i class="fas fa-sign-in-alt"></i> Conectare';
            loginLink.style.display = 'block';
            dropdownContent.insertBefore(loginLink, dropdownContent.firstChild);
        }
    }
    
    // IMPORTANT: Reinițializează dropdown-ul DUPĂ ce am actualizat conținutul
    // Asta asigură că event listener-ul funcționează corect
    if (typeof window.initDropdown === 'function') {
        setTimeout(window.initDropdown, 150);
    } else if (typeof initDropdown === 'function') {
        setTimeout(initDropdown, 150);
    }
}

// Inițializează când se încarcă pagina
function initHeaderAuth() {
    // Așteaptă puțin pentru a se asigura că DOM-ul e complet încărcat
    setTimeout(function() {
        updateHeaderAuth();
        
        // Reîmprospătează la fiecare 10 secunde (dar NU la fiecare secundă pentru a evita spam)
        setInterval(function() {
            updateHeaderAuth();
            // Reinițializează dropdown-ul după fiecare actualizare
            if (typeof window.initDropdown === 'function') {
                setTimeout(window.initDropdown, 150);
            } else if (typeof initDropdown === 'function') {
                setTimeout(initDropdown, 150);
            }
        }, 10000);
    }, 300);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initHeaderAuth);
} else {
    initHeaderAuth();
}

