<?php



// ---------------------------------------------------------------------
// Dodatkowe pola użytkownika w panelu admina
//



function wyswietl_pola_profilu_uzytkownika($user) {
    if (!current_user_can('edit_user', $user->ID)) {
        return;
    }

    // Dodatkowe informacje o firmie (nie będące standardowymi polami billingowymi WooCommerce)
    $extra_fields = [
        'rodzaj_dzialalnosci'  => 'Rodzaj działalności',
        'billing_vat'          => 'NIP',
        'customer_note'        => 'Dodatkowe informacje',
    ];

    echo '<h3>Dodatkowe informacje o firmie</h3>';
    echo '<table class="form-table">';

    foreach ($extra_fields as $field => $label) {
        $value = get_user_meta($user->ID, $field, true);
        echo '<tr>';
        echo '<th><label for="' . esc_attr($field) . '">' . esc_html($label) . '</label></th>';
        echo '<td>';
        if ($field === 'customer_note') {
            echo '<textarea name="' . esc_attr($field) . '" id="' . esc_attr($field) . '" rows="5" class="regular-text">' . esc_textarea($value) . '</textarea>';
        } else {
            echo '<input type="text" name="' . esc_attr($field) . '" id="' . esc_attr($field) . '" value="' . esc_attr($value) . '" class="regular-text" />';
        }
        echo '</td>';
        echo '</tr>';
    }
    echo '</table>';
}

// Zapisywanie danych z profilu użytkownika
function zapisz_pola_profilu_uzytkownika($user_id) {
    if (!current_user_can('edit_user', $user_id)) {
        return false;
    }

    // Zapisujemy tylko niestandardowe pola, które nie są billingowymi WooCommerce
    $fields = [
        'rodzaj_dzialalnosci',
        'billing_vat',
        'customer_note',
    ];

    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            $value = ($field === 'customer_note')
                ? sanitize_textarea_field($_POST[$field])
                : sanitize_text_field($_POST[$field]);
            update_user_meta($user_id, $field, $value);
        }
    }
}

// Filtracja pól billingowych (dla pola 'billing_vat')
function custom_woocommerce_billing_fields($fields) {
    // Ustawienie pola billing_vat jako pole billingowe
    $fields['billing_vat'] = [
        'label'       => __('NIP', 'storefront-child'),
        'placeholder' => _x('NIP', 'placeholder', 'storefront-child'),
        'required'    => true,
        'clear'       => true,
        'type'        => 'text',
    ];
    return $fields;
}

// Hooki do WordPressa
add_action('show_user_profile', 'wyswietl_pola_profilu_uzytkownika');
add_action('edit_user_profile', 'wyswietl_pola_profilu_uzytkownika');
add_action('personal_options_update', 'zapisz_pola_profilu_uzytkownika');
add_action('edit_user_profile_update', 'zapisz_pola_profilu_uzytkownika');

// Dodanie filtru do WooCommerce, aby 'billing_vat' było polem billingowym
add_filter('woocommerce_billing_fields', 'custom_woocommerce_billing_fields');
