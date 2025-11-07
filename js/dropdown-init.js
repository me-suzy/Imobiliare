// Script pentru inițializarea dropdown-ului pe toate paginile
// Se include în toate paginile pentru a funcționa dropdown-ul

document.addEventListener('DOMContentLoaded', function() {
    initDropdown();
});

// Funcție pentru inițializarea dropdown-ului
function initDropdown() {
    const dropdown = document.querySelector('.dropdown');
    const userMenuBtn = document.querySelector('.user-menu');
    
    if (!userMenuBtn || !dropdown) {
        // Dacă nu există elementele, nu face nimic
        return;
    }
    
    // Șterge event listeners existente (dacă există)
    const newBtn = userMenuBtn.cloneNode(true);
    userMenuBtn.parentNode.replaceChild(newBtn, userMenuBtn);
    
    // Adaugă event listener nou pentru click
    newBtn.addEventListener('click', function(event) {
        event.preventDefault();
        event.stopPropagation();
        
        // Toggle dropdown
        const dropdown = document.querySelector('.dropdown');
        if (dropdown) {
            dropdown.classList.toggle('active');
        }
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        const dropdown = document.querySelector('.dropdown');
        if (dropdown) {
            const clickedElement = event.target;
            const isDropdownButton = clickedElement.closest('.user-menu');
            const isInDropdownContent = clickedElement.closest('.dropdown-content');
            
            // NU închide dropdown-ul dacă click-ul e pe buton sau în content
            if (!isDropdownButton && !isInDropdownContent) {
                dropdown.classList.remove('active');
            }
        }
    });
}

// Reinițializează dropdown-ul (pentru pagini care se reîncarcă dinamic)
if (typeof window.reinitDropdown === 'undefined') {
    window.reinitDropdown = initDropdown;
}

