<?php




/**
 * Dodawanie dodatkowych pól do profilu użytkownika w panelu administratora
 * @param WP_User $user Obiekt użytkownika
 */
function wyswietl_pola_profilu_uzytkownika($user) {
    // Sprawdzenie uprawnień
    if (!current_user_can('edit_user', $user->ID)) {
        return;
    }
    
    // Pobieranie zapisanych wartości
    $rodzaj_dzialalnosci = get_user_meta($user->ID, 'rodzaj_dzialalnosci', true);
    $nip = get_user_meta($user->ID, 'billing_vat', true);
    $dodatkowe_info = get_user_meta($user->ID, 'customer_note', true);
    ?>
    
    <h3>Dodatkowe informacje o firmie</h3>
    <table class="form-table">
        <tr>
            <th><label for="rodzaj_dzialalnosci">Rodzaj działalności</label></th>
            <td>
                <input type="text" name="rodzaj_dzialalnosci" id="rodzaj_dzialalnosci" value="<?php echo esc_attr($rodzaj_dzialalnosci); ?>" class="regular-text" />
            </td>
        </tr>
        <tr>
            <th><label for="billing_vat">NIP</label></th>
            <td>
                <input type="text" name="billing_vat" id="billing_vat" value="<?php echo esc_attr($nip); ?>" class="regular-text" />
            </td>
        </tr>
        <tr>
            <th><label for="customer_note">Dodatkowe informacje</label></th>
            <td>
                <textarea name="customer_note" id="customer_note" rows="5" class="regular-text"><?php echo esc_textarea($dodatkowe_info); ?></textarea>
            </td>
        </tr>
    </table>
    <?php
}



/**
 * Zapisuje zmiany w dodatkowych polach profilu użytkownika
 * 
 * @param int $user_id ID użytkownika
 */
function zapisz_pola_profilu_uzytkownika($user_id) {
    // Sprawdzenie uprawnień
    if (!current_user_can('edit_user', $user_id)) {
        return false;
    }
    
    // Zapisywanie danych z formularza
    $fields = [
        'rodzaj_dzialalnosci',
        'billing_vat',
        'customer_note'
    ];
    
    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            if ($field === 'customer_note') {
                update_user_meta($user_id, $field, sanitize_textarea_field($_POST[$field]));
            } else {
                update_user_meta($user_id, $field, sanitize_text_field($_POST[$field]));
            }
        }
    }
}

// Podpięcie funkcji do odpowiednich akcji
add_action('show_user_profile', 'wyswietl_pola_profilu_uzytkownika');
add_action('edit_user_profile', 'wyswietl_pola_profilu_uzytkownika');
add_action('personal_options_update', 'zapisz_pola_profilu_uzytkownika');
add_action('edit_user_profile_update', 'zapisz_pola_profilu_uzytkownika');


