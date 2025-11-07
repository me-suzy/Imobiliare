// Template pentru header - se include în toate paginile
// Funcție pentru a încărca header-ul în pagină
function initHeader() {
    // Verifică dacă header-ul există deja
    if (document.querySelector('.header-initialized')) {
        return;
    }
    
    // Marchează header-ul ca inițializat
    const header = document.querySelector('.header');
    if (header) {
        header.classList.add('header-initialized');
    }
    
    // Încarcă numărul de notificări
    loadNotificationsCount();
    
    // Verifică dacă e admin
    checkAdminAccess();
}

// Încarcă numărul de notificări
async function loadNotificationsCount() {
    try {
        const authCheck = await Auth.check();
        if (authCheck && authCheck.autentificat) {
            const result = await API.get('notificari.php');
            const badge = document.getElementById('notificari-badge');
            if (badge && result.necitite > 0) {
                badge.textContent = result.necitite;
                badge.style.display = 'inline-block';
            } else if (badge) {
                badge.style.display = 'none';
            }
        }
    } catch (error) {
        // Ignoră erorile pentru utilizatorii neautentificați
        console.log('Nu sunt notificări disponibile');
    }
}

// Verifică dacă e admin și afișează link-ul admin
async function checkAdminAccess() {
    try {
        const authCheck = await Auth.check();
        if (authCheck && authCheck.autentificat && authCheck.user && authCheck.user.tip_cont === 'admin') {
            const adminLink = document.getElementById('admin-link');
            if (adminLink) {
                adminLink.style.display = 'block';
            }
        }
    } catch (error) {
        // Ignoră erorile
    }
}

// Inițializează header-ul când se încarcă pagina
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initHeader);
} else {
    initHeader();
}

