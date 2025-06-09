<?php
add_action('admin_menu', function() {
    add_menu_page(
        'Lista klientów',
        'Lista klientów',
        'manage_options',
        'adm_edit_customers',
        'adm_render_edit_customers_page',
        'dashicons-admin-users',
        56
    );
});

// AJAX handler do zmiany roli inline
add_action('wp_ajax_adm_change_user_role_inline', 'adm_handle_change_user_role_inline');

function adm_handle_change_user_role_inline() {
    error_log('AJAX Request: ' . print_r($_POST, true));
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Brak uprawnień');
        return;
    }
    
    if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'adm_change_role_inline')) {
        error_log('Nonce error: ' . ($_POST['_wpnonce'] ?? 'brak nonce'));
        wp_send_json_error('Błędny nonce');
        return;
    }
    
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    $role = isset($_POST['role']) ? sanitize_text_field($_POST['role']) : '';
    
    if (!$user_id || !$role) {
        wp_send_json_error('Brak wymaganych danych');
        return;
    }
    
    $user = get_user_by('id', $user_id);
    if (!$user) {
        wp_send_json_error('Nie znaleziono użytkownika');
        return;
    }
    
    if (!get_role($role)) {
        wp_send_json_error('Nieprawidłowa rola');
        return;
    }
    
    $user->set_role($role);
    
    wp_send_json_success(array(
        'message' => 'Rola została zmieniona',
        'new_role' => $role,
        'user_id' => $user_id
    ));
}

// NOWY AJAX handler do edycji danych użytkownika
add_action('wp_ajax_adm_update_user_data', 'adm_handle_update_user_data');

function adm_handle_update_user_data() {
   
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Brak uprawnień');
        return;
    }
    
    if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'adm_update_user_data')) {
        wp_send_json_error('Błędny nonce');
        return;
    }
    
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    
    if (!$user_id) {
        wp_send_json_error('Brak ID użytkownika');
        return;
    }
    
    $user = get_user_by('id', $user_id);
    if (!$user) {
        wp_send_json_error('Nie znaleziono użytkownika');
        return;
    }
    
    $updated_fields = array();
    $errors = array();
    
    // Aktualizuj dane użytkownika
    $user_data = array('ID' => $user_id);
    
    // Imię
    if (isset($_POST['first_name'])) {
        $first_name = sanitize_text_field($_POST['first_name']);
        $user_data['first_name'] = $first_name;
        $updated_fields['first_name'] = $first_name;
    }
    
    // Nazwisko
    if (isset($_POST['last_name'])) {
        $last_name = sanitize_text_field($_POST['last_name']);
        $user_data['last_name'] = $last_name;
        $updated_fields['last_name'] = $last_name;
    }
    
    // Rola
    if (isset($_POST['role'])) {
        $role = sanitize_text_field($_POST['role']);
        if (get_role($role)) {
            $user_data['role'] = $role;
            $updated_fields['role'] = $role;
        } else {
            $errors[] = 'Nieprawidłowa rola';
        }
    }
    
    // Aktualizuj podstawowe dane użytkownika
    if (count($user_data) > 1) {
        $result = wp_update_user($user_data);
        if (is_wp_error($result)) {
            $errors[] = $result->get_error_message();
        }
    }
    
    // Aktualizuj meta dane
    $meta_fields = array(
        'billing_company' => 'company',
        'billing_vat' => 'vat', 
        'billing_phone' => 'phone'
    );
    
    foreach ($meta_fields as $meta_key => $post_key) {
        if (isset($_POST[$post_key])) {
            $value = sanitize_text_field($_POST[$post_key]);
            update_user_meta($user_id, $meta_key, $value);
            $updated_fields[$post_key] = $value;
        }
    }
    
    if (!empty($errors)) {
        wp_send_json_error(implode(', ', $errors));
        return;
    }
    
    wp_send_json_success(array(
        'message' => 'Dane zostały zaktualizowane',
        'updated_fields' => $updated_fields,
        'user_id' => $user_id
    ));
}

function adm_render_edit_customers_page() {
    $allowed_roles = ['subscriber', 'customer', 'zainteresowany_oferta'];
    
    $args = [
        'role__in' => $allowed_roles,
        'number' => 200,
    ];
    $users = get_users($args);

    if (isset($_POST['adm_change_role_user_id'], $_POST['adm_change_role_new_role']) && check_admin_referer('adm_change_role')) {
        $user_id = intval($_POST['adm_change_role_user_id']);
        $new_role = sanitize_text_field($_POST['adm_change_role_new_role']);
        $user = get_user_by('id', $user_id);
        if ($user) {
            $user->set_role($new_role);
            echo '<div class="updated"><p>Rola użytkownika została zmieniona.</p></div>';
        }
    }

    echo '<h1>Lista klientów</h1>';
    
    $display_roles = [
        'subscriber' => 'Subskrybent',
        'customer' => 'Klient', 
        'zainteresowany_oferta' => 'Zainteresowany ofertą'
    ];
    
    echo '<table class="widefat" id="adm-customers-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>E-mail</th>
            <th>Imię i nazwisko</th>
            <th>Rola</th>
            <th>Nazwa firmy</th>
            <th>NIP</th>
            <th>Telefon</th>
            <th>Zamówienia</th>
            <th>Suma wydatków</th>
            <th>Ostatnio aktywny</th>
            <th>Data rejestracji</th>
        </tr>
    </thead>
    <tbody>';
    foreach ($users as $user) {
        $current_role = $user->roles[0] ?? '';
        
        $company = get_user_meta($user->ID, 'billing_company', true);
        $vat = get_user_meta($user->ID, 'billing_vat', true);
        $phone = get_user_meta($user->ID, 'billing_phone', true);
        
        $order_count = 0;
        $total_spent = 0;
        $last_active = '';
        
        if (function_exists('wc_get_customer_order_count')) {
            $order_count = wc_get_customer_order_count($user->ID);
        }
        
        if (function_exists('wc_get_customer_total_spent')) {
            $total_spent = wc_get_customer_total_spent($user->ID);
        }
        
        $last_active_timestamp = get_user_meta($user->ID, 'wc_last_active', true);
        if ($last_active_timestamp) {
            $last_active = date('Y-m-d H:i', $last_active_timestamp);
        } else {
            if (function_exists('wc_get_orders')) {
                $last_orders = wc_get_orders([
                    'customer' => $user->ID,
                    'limit' => 1,
                    'orderby' => 'date',
                    'order' => 'DESC'
                ]);
                if (!empty($last_orders)) {
                    $last_active = $last_orders[0]->get_date_created()->date('Y-m-d H:i');
                }
            }
        }
        
        if (empty($last_active)) {
            $last_active = 'Nigdy';
        }
        
        $registration_date = date('Y-m-d H:i', strtotime($user->user_registered));
        $full_name = trim($user->first_name . ' ' . $user->last_name);
        
        echo '<tr data-user-id="' . $user->ID . '" class="adm-editable-row">';
        echo '<td class="adm-readonly">' . $user->ID . '</td>';
        echo '<td class="adm-readonly">' . esc_html($user->user_email) . '</td>';
        echo '<td class="adm-editable" data-field="name" data-first-name="' . esc_attr($user->first_name) . '" data-last-name="' . esc_attr($user->last_name) . '">' . esc_html($full_name) . '</td>';
        echo '<td class="adm-editable adm-role-cell" data-field="role" data-role="' . esc_attr($current_role) . '">' . esc_html($display_roles[$current_role] ?? $current_role) . '</td>';
        echo '<td class="adm-editable" data-field="company">' . esc_html($company) . '</td>';
        echo '<td class="adm-editable" data-field="vat">' . esc_html($vat) . '</td>';
        echo '<td class="adm-editable" data-field="phone">' . esc_html($phone) . '</td>';
        echo '<td class="adm-readonly">' . intval($order_count) . '</td>';
        echo '<td class="adm-readonly">' . wc_price($total_spent) . '</td>';
        echo '<td class="adm-readonly">' . esc_html($last_active) . '</td>';
        echo '<td class="adm-readonly">' . esc_html($registration_date) . '</td>';
        echo '</tr>';
    }
    echo '</tbody></table>';

    ?>
    <style>
    #adm-customers-table {
        font-size: 13px;
    }
    #adm-customers-table th,
    #adm-customers-table td {
        padding: 8px 4px;
        text-align: left;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 150px;
    }
    #adm-customers-table th:nth-child(1),
    #adm-customers-table td:nth-child(1) {
        max-width: 50px;
    }
    #adm-customers-table th:nth-child(2),
    #adm-customers-table td:nth-child(2) {
        max-width: 200px;
    }
    #adm-customers-table th:nth-child(8),
    #adm-customers-table td:nth-child(8) {
        max-width: 80px;
        text-align: center;
    }
    #adm-customers-table th:nth-child(9),
    #adm-customers-table td:nth-child(9) {
        max-width: 100px;
        text-align: right;
    }
    
    /* Nowe style dla edycji wiersza */
    .adm-editable-row {
        cursor: pointer;
        transition: background-color 0.2s;
    }
    .adm-editable-row:hover {
        background-color: #f8f9fa;
    }
    .adm-editable-row.adm-editing {
        background-color: #fff3cd;
        box-shadow: 0 0 0 2px #ffc107;
    }
    .adm-editable {
        position: relative;
    }
    .adm-editable input, .adm-editable select {
        width: 100%;
        padding: 4px;
        border: 1px solid #ddd;
        font-size: 13px;
        background: white;
        box-sizing: border-box;
    }
    .adm-editable div {
        width: 100%;
    }
    .adm-readonly {
        background-color: #f9f9f9;
        opacity: 0.7;
    }
    .adm-edit-actions {
        position: absolute;
        top: -5px;
        right: -5px;
        z-index: 1000;
        background: white;
        border: 1px solid #ddd;
        border-radius: 4px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        padding: 4px;
    }
    .adm-edit-actions button {
        margin: 0 2px;
        padding: 4px 8px;
        font-size: 11px;
        border: none;
        border-radius: 3px;
        cursor: pointer;
    }
    .adm-save-btn {
        background: #007cba;
        color: white;
    }
    .adm-cancel-btn {
        background: #ddd;
        color: #333;
    }
    .adm-tooltip {
        position: absolute;
        z-index: 9999;
        background: #fffbe6;
        color: #333;
        border: 1px solid #ffe58f;
        border-radius: 4px;
        padding: 6px 14px;
        font-size: 13px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        pointer-events: none;
        opacity: 0;
        transition: opacity 0.2s;
        white-space: nowrap;
    }
    .adm-tooltip.adm-tooltip-visible {
        opacity: 1;
    }
    </style>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const allRoles = <?php echo json_encode($display_roles); ?>;
        
        if (typeof ajaxurl === 'undefined') {
            window.ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
        }
        
        const roleNonce = '<?php echo wp_create_nonce('adm_change_role_inline'); ?>';
        const updateNonce = '<?php echo wp_create_nonce('adm_update_user_data'); ?>';
        
        // Tooltip
        let tooltip = document.createElement('div');
        tooltip.className = 'adm-tooltip';
        tooltip.textContent = 'Kliknij dwukrotnie aby edytować wiersz';
        document.body.appendChild(tooltip);
        
        let currentEditingRow = null;
        
        // Obsługa hover i tooltip
        document.querySelectorAll('.adm-editable-row').forEach(function(row) {
            let tooltipTimeout;
            
            row.addEventListener('mouseenter', function() {
                if (currentEditingRow) return;
                
                tooltipTimeout = setTimeout(function() {
                    const rect = row.getBoundingClientRect();
                    tooltip.style.left = (window.scrollX + rect.left + rect.width/2 - tooltip.offsetWidth/2) + 'px';
                    tooltip.style.top = (window.scrollY + rect.top - tooltip.offsetHeight - 8) + 'px';
                    tooltip.classList.add('adm-tooltip-visible');
                }, 1000);
            });
            
            row.addEventListener('mouseleave', function() {
                clearTimeout(tooltipTimeout);
                tooltip.classList.remove('adm-tooltip-visible');
            });
            
            // Obsługa dwukrotnego kliknięcia w wiersz
            row.addEventListener('dblclick', function(e) {
                if (currentEditingRow) return;
                
                clearTimeout(tooltipTimeout);
                tooltip.classList.remove('adm-tooltip-visible');
                
                startRowEdit(row);
            });
        });
        
        function startRowEdit(row) {
            if (currentEditingRow) return;
            
            currentEditingRow = row;
            row.classList.add('adm-editing');
            
            const userId = row.getAttribute('data-user-id');
            const editableCells = row.querySelectorAll('.adm-editable');
            const originalData = {};
            
            // Zapisz oryginalne dane i stwórz pola edycji
            editableCells.forEach(function(cell) {
                const field = cell.getAttribute('data-field');
                originalData[field] = {
                    element: cell,
                    content: cell.innerHTML,
                    textContent: cell.textContent.trim()
                };
                
                if (field === 'role') {
                    // Pole roli - select
                    const currentRole = cell.getAttribute('data-role');
                    const select = document.createElement('select');
                    select.style.width = '100%';
                    
                    for (const roleKey in allRoles) {
                        const opt = document.createElement('option');
                        opt.value = roleKey;
                        opt.textContent = allRoles[roleKey];
                        if (roleKey === currentRole) opt.selected = true;
                        select.appendChild(opt);
                    }
                    
                    cell.innerHTML = '';
                    cell.appendChild(select);
                    
                } else if (field === 'name') {
                    // Pole imię i nazwisko - dwa inputy
                    const firstName = cell.getAttribute('data-first-name') || '';
                    const lastName = cell.getAttribute('data-last-name') || '';
                    
                    const container = document.createElement('div');
                    container.style.display = 'flex';
                    container.style.gap = '4px';
                    container.style.width = '100%';
                    
                    const firstNameInput = document.createElement('input');
                    firstNameInput.type = 'text';
                    firstNameInput.value = firstName;
                    firstNameInput.placeholder = 'Imię';
                    firstNameInput.style.flex = '1';
                    firstNameInput.style.minWidth = '0';
                    firstNameInput.setAttribute('data-name-field', 'first');
                    
                    const lastNameInput = document.createElement('input');
                    lastNameInput.type = 'text';
                    lastNameInput.value = lastName;
                    lastNameInput.placeholder = 'Nazwisko';
                    lastNameInput.style.flex = '1';
                    lastNameInput.style.minWidth = '0';
                    lastNameInput.setAttribute('data-name-field', 'last');
                    
                    container.appendChild(firstNameInput);
                    container.appendChild(lastNameInput);
                    
                    cell.innerHTML = '';
                    cell.appendChild(container);
                    
                } else {
                    // Zwykłe pole tekstowe
                    const input = document.createElement('input');
                    input.type = 'text';
                    input.value = originalData[field].textContent;
                    cell.innerHTML = '';
                    cell.appendChild(input);
                }
            });
            
            // Dodaj przyciski akcji
            const actionsDiv = document.createElement('div');
            actionsDiv.className = 'adm-edit-actions';
            
            const saveBtn = document.createElement('button');
            saveBtn.textContent = 'Zapisz';
            saveBtn.className = 'adm-save-btn';
            
            const cancelBtn = document.createElement('button');
            cancelBtn.textContent = 'Anuluj';
            cancelBtn.className = 'adm-cancel-btn';
            
            actionsDiv.appendChild(saveBtn);
            actionsDiv.appendChild(cancelBtn);
            row.style.position = 'relative';
            row.appendChild(actionsDiv);
            
            // Focus na pierwszym polu
            const firstInput = row.querySelector('input, select');
            if (firstInput) firstInput.focus();
            
            // Obsługa zapisywania
            saveBtn.addEventListener('click', function() {
                saveRowEdit(row, userId, originalData);
            });
            
            // Obsługa anulowania
            cancelBtn.addEventListener('click', function() {
                cancelRowEdit(row, originalData);
            });
            
            // Obsługa klawiatury tylko podczas edycji (focus na input/select)
            row.querySelectorAll('input, select').forEach(function(input) {
                input.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' && e.ctrlKey) {
                        e.preventDefault();
                        saveRowEdit(row, userId, originalData);
                    } else if (e.key === 'Escape') {
                        e.preventDefault();
                        cancelRowEdit(row, originalData);
                    }
                });
            });
        }
        
        function saveRowEdit(row, userId, originalData) {
            const formData = new FormData();
            formData.append('action', 'adm_update_user_data');
            formData.append('user_id', userId);
            formData.append('_wpnonce', updateNonce);
            
            // Zbierz dane z pól edycji
            row.querySelectorAll('.adm-editable').forEach(function(cell) {
                const field = cell.getAttribute('data-field');
                
                if (field === 'role') {
                    const select = cell.querySelector('select');
                    if (select) {
                        formData.append('role', select.value);
                    }
                } else if (field === 'name') {
                    const container = cell.querySelector('div');
                    const inputs = container ? container.querySelectorAll('input') : [];
                    if (inputs.length >= 2) {
                        formData.append('first_name', inputs[0].value.trim());
                        formData.append('last_name', inputs[1].value.trim());
                    }
                } else {
                    const input = cell.querySelector('input');
                    if (input) {
                        formData.append(field, input.value);
                    }
                }
            });
            
            // Pokaż loading
            row.querySelector('.adm-edit-actions').innerHTML = 'Zapisywanie...';
            
            fetch(ajaxurl, {
                method: 'POST',
                credentials: 'same-origin',
                body: formData
            })
            .then(response => response.json())
            .then(resp => {
                if (resp.success) {
                    // Aktualizuj wyświetlane dane
                    updateRowDisplay(row, resp.data.updated_fields, originalData);
                    finishRowEdit(row);
                    console.log('Dane zaktualizowane pomyślnie');
                } else {
                    console.error('Błąd aktualizacji:', resp.data);
                    alert('Błąd: ' + (resp.data || 'Nieznany błąd'));
                    cancelRowEdit(row, originalData);
                }
            })
            .catch(error => {
                console.error('Błąd połączenia:', error);
                alert('Błąd połączenia!');
                cancelRowEdit(row, originalData);
            });
        }
        
        function updateRowDisplay(row, updatedFields, originalData) {
            // Najpierw przywróć zwykły widok dla pola imię i nazwisko, jeśli zmieniono first_name lub last_name
            if (updatedFields['first_name'] !== undefined || updatedFields['last_name'] !== undefined) {
                const nameCell = row.querySelector('.adm-editable[data-field="name"]');
                if (nameCell) {
                    const firstName = updatedFields['first_name'] || nameCell.getAttribute('data-first-name') || '';
                    const lastName = updatedFields['last_name'] || nameCell.getAttribute('data-last-name') || '';
                    nameCell.innerHTML = '';
                    nameCell.textContent = (firstName + ' ' + lastName).trim();
                    nameCell.setAttribute('data-first-name', firstName);
                    nameCell.setAttribute('data-last-name', lastName);
                }
            }
            for (const field in updatedFields) {
                if (field === 'first_name' || field === 'last_name') continue;
                const cell = row.querySelector('.adm-editable[data-field="' + field + '"]');
                if (!cell) continue;
                if (field === 'role') {
                    cell.textContent = allRoles[updatedFields[field]] || updatedFields[field];
                    cell.setAttribute('data-role', updatedFields[field]);
                } else {
                    cell.textContent = updatedFields[field];
                }
            }
        }
        
        function cancelRowEdit(row, originalData) {
            // Przywróć oryginalne dane
            for (const field in originalData) {
                originalData[field].element.innerHTML = originalData[field].content;
            }
            finishRowEdit(row);
        }
        
        function finishRowEdit(row) {
            row.classList.remove('adm-editing');
            const actions = row.querySelector('.adm-edit-actions');
            if (actions) {
                actions.remove();
            }
            currentEditingRow = null;
        }
    });
    </script>
    <?php
}
?>