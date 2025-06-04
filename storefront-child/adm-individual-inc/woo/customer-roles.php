
<?php
// Dodaj AJAX handler
add_action('wp_ajax_adm_change_user_role', function() {
    if (!current_user_can('manage_options')) wp_send_json_error('Brak uprawnień');
    check_ajax_referer('adm_change_role_nonce');
    $user_id = intval($_POST['user_id']);
    $role = sanitize_text_field($_POST['role']);
    $user = get_user_by('id', $user_id);
    if (!$user) wp_send_json_error('Nie znaleziono użytkownika');
    $user->set_role($role);
    wp_send_json_success();
});




function ml_change_group_to_customer($email) {
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) return;

    $data = [
        'email' => $email
    ];

    $ml_jdata = json_encode($data, JSON_UNESCAPED_UNICODE);

    $request_type = 'POST';
    $request_group = '112998441'; // <-- tutaj wpisz ID grupy docelowej

    $CURLOPT_URL = 'https://api.mailerlite.com/api/v2/groups/' . $request_group . '/subscribers';

    if (!defined('ML_API_KEY')) define('ML_API_KEY', 'dc52e84d9ab80759d811ac3fd3aec497');

    $curl_data = array(
        CURLOPT_URL => $CURLOPT_URL,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => $request_type,
        CURLOPT_POSTFIELDS => $ml_jdata,
        CURLOPT_HTTPHEADER => array(
            'X-MailerLite-ApiKey: ' . ML_API_KEY,
            'Content-Type: application/json',
        )
    );

    $curl = curl_init();
    curl_setopt_array($curl, $curl_data);

    $response = curl_exec($curl);
    curl_close($curl);
}

// Wywołaj funkcję po zmianie roli na 'customer'
add_action('set_user_role', function($user_id, $new_role, $old_roles) {
    if ($new_role === 'customer') {
        $user = get_userdata($user_id);
        if ($user && !empty($user->user_email)) {
            ml_change_group_to_customer($user->user_email);
        }
    }
}, 10, 3);



// Dodaj JS globalnie w panelu admina, obsługa SPA WooCommerce Admin
add_action('admin_print_footer_scripts', function() {
    ?>
    <script>
 /*   console.log("Start user-roles");
    (function() {
        function addRoleColumn() {
            // Szukaj wierszy klientów po klasie Reactowej tabeli
            const table = document.querySelector('.woocommerce-layout__table, [data-testid="customer-table"] table');
            if (!table) return setTimeout(addRoleColumn, 500);

            // Dodaj nagłówek kolumny
            const thead = table.querySelector('thead tr');
            if (thead && !thead.querySelector('.rola-col')) {
                const th = document.createElement('th');
                th.textContent = 'Rola';
                th.className = 'rola-col';
                thead.appendChild(th);
            }

            // Dodaj select do każdej komórki
            const rows = table.querySelectorAll('tbody tr');
            rows.forEach(row => {
                if (row.querySelector('.rola-col')) return; // już jest
                // Szukaj linku do edycji użytkownika lub id w atrybucie data-id
                let userId = row.querySelector('a[href*="user-edit.php?user_id="]');
                let uid = null;
                if (userId) {
                    const idMatch = userId.href.match(/user_id=(\d+)/);
                    if (idMatch) uid = idMatch[1];
                } else if (row.dataset.id) {
                    uid = row.dataset.id;
                }
                // Pobierz aktualną rolę z atrybutu data-role lub z tekstu w komórce (fallback)
                let currentRole = row.dataset.role || '';
                if (!currentRole) {
                    const roleCell = Array.from(row.children).find(td => td.textContent && td.textContent.match(/klient|subskrybent|zainteresowany_oferta|shop_manager|administrator/i));
                    if (roleCell) {
                        if (roleCell.textContent.match(/subskrybent/i)) currentRole = 'subscriber';
                        else if (roleCell.textContent.match(/zainteresowany_oferta/i)) currentRole = 'zainteresowany_oferta';
                        else if (roleCell.textContent.match(/shop_manager/i)) currentRole = 'shop_manager';
                        else if (roleCell.textContent.match(/administrator/i)) currentRole = 'administrator';
                        else currentRole = 'customer';
                    }
                }
                // Debug: pokaż dane każdego wiersza klienta
                console.log('[rola-debug] Wiersz klienta:', {
                    row: row,
                    uid: uid,
                    currentRole: currentRole,
                    rowHTML: row.innerHTML
                });
                if (!uid) return;

                const td = document.createElement('td');
                td.className = 'rola-col';
                td.innerHTML = `
                    <select data-user-id="${uid}">
                        <option value="customer">Klient</option>
                        <option value="subscriber">Subskrybent</option>
                        <option value="zainteresowany_oferta">Zainteresowany ofertą</option>
                        <option value="shop_manager">Shop Manager</option>
                        <option value="administrator">Administrator</option>
                    </select>
                `;
                td.querySelector('select').value = currentRole || 'customer';
                td.querySelector('select').addEventListener('change', function() {
                    const newRole = this.value;
                    fetch(ajaxurl, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                        body: `action=adm_change_user_role&user_id=${uid}&role=${newRole}&_wpnonce=${admChangeRole.nonce}`
                    })
                    .then(r => r.json())
                    .then(resp => {
                        if (resp.success) {
                            this.style.background = '#cfc';
                        } else {
                            this.style.background = '#fcc';
                            alert('Błąd zmiany roli: ' + (resp.data || ''));
                        }
                    });
                });
                row.appendChild(td);
            });
        }
        // Uruchom na starcie i po każdej zmianie adresu (SPA)
        addRoleColumn();
        document.body.addEventListener('wc-admin_navigation', addRoleColumn);
    })();
    console.log("Finish user-roles");
 */
  </script>
    <?php
});