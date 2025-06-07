document.addEventListener('DOMContentLoaded', function () {
    // ==== KONFIGURACJA ====
    const hideByRole = {
        'administrator': true,
        'editor': true,
        'author': true,
        'subscriber': true
    };

    const hideByUser = {
        'admin': false,
        'jan.kowalski': true,
        42: true // ID użytkownika jako liczba
    };

    const filterMode = 'whitelist'; // 'whitelist' = pokazuj tylko poniższe, 'blacklist' = ukrywaj tylko poniższe
    const filterItems = [
        '#menu-pages',
        '#menu-appearance',
        '#menu-posts',
        '#menu-media',
        '#menu-settings'
    ];

    // ==== POBIERZ INFORMACJE Z WORDPRESSA ====
    const currentUserLogin = window.wp && wp.data ? wp.data.select('core').getCurrentUser().slug : null;
    const currentUserId = window.wp && wp.data ? wp.data.select('core').getCurrentUser().id : null;
    const currentUserRole = window.wp && wp.data ? wp.data.select('core').getCurrentUser().roles?.[0] : null;

    // ==== DECYZJA: CZY DOMYŚLNIE UKRYWAĆ? ====
    let hidden = false;

    // Sprawdzamy zapisane preferencje użytkownika w localStorage
    const userPreference = localStorage.getItem('menuState');
    if (userPreference !== null) {
        hidden = (userPreference === 'hidden');
    } else {
        // Jeśli brak preferencji, ustawiamy na podstawie roli lub użytkownika
        if (typeof hideByUser[currentUserLogin] !== 'undefined') {
            hidden = hideByUser[currentUserLogin];
        } else if (typeof hideByUser[currentUserId] !== 'undefined') {
            hidden = hideByUser[currentUserId];
        } else if (typeof hideByRole[currentUserRole] !== 'undefined') {
            hidden = hideByRole[currentUserRole];
        }
    }

    // ==== PRZYCISK ====
    const adminMenu = document.querySelector('#adminmenu');
    if (!adminMenu) return;

    const li = document.createElement('li');
    li.id = 'menu-toggle-custom';
    li.className = 'custom-toggle-wrapper';
    li.style.padding = '10px';

    const btn = document.createElement('button');
    btn.className = 'button button-primary';
    btn.style.width = '100%';
    btn.textContent = hidden ? 'Pokaż menu' : 'Ukryj menu';

    li.appendChild(btn);
    adminMenu.insertBefore(li, adminMenu.firstChild);

    const allMenuItems = Array.from(adminMenu.querySelectorAll('li.menu-top'));
    const shouldAffect = (item) => {
        const match = filterItems.some(sel => item.matches(sel));
        return filterMode === 'whitelist' ? !match : match;
    };

    let affected = [];

    // ==== UKRYJ DOMYŚLNIE ====
    if (hidden) {
        affected = [];
        allMenuItems.forEach(item => {
            if (item.id === 'menu-toggle-custom') return;
            if (shouldAffect(item)) {
                affected.push(item);
                item.style.display = 'none';
            }
        });
    }

    // ==== OBSŁUGA KLIKNIĘCIA ====
    btn.addEventListener('click', function (e) {
        e.preventDefault();

        if (!hidden) {
            affected = [];
            allMenuItems.forEach(item => {
                if (item.id === 'menu-toggle-custom') return;
                if (shouldAffect(item)) {
                    affected.push(item);
                    item.style.display = 'none';
                }
            });
            btn.textContent = 'Pokaż menu';
            localStorage.setItem('menuState', 'hidden'); // Zapisz preferencję
        } else {
            affected.forEach(item => item.style.display = '');
            affected = [];
            btn.textContent = 'Ukryj menu';
            localStorage.setItem('menuState', 'visible'); // Zapisz preferencję
        }

        hidden = !hidden;
    });
});
