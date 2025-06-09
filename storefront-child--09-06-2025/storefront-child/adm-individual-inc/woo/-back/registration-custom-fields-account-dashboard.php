<?php








// ---------------------------------------------------------------------
// Dodatkowe pola użytkownika w panelu klienta "Moje konto"
//




// Dodaj pole NIP do formularza edycji adresu rozliczeniowego (billing)
add_filter('woocommerce_billing_fields', function($fields) {
    $fields['billing_vat'] = array(
        'label'       => __('NIP', 'storefront-child'),
        'placeholder' => _x('NIP', 'placeholder', 'storefront-child'),
        'required'    => true,
        'class'       => array('form-row-wide'),
        'clear'       => true,
        'priority'    => 120,
        'type'        => 'text',
    );
    return $fields;
});
add_filter('woocommerce_shipping_fields', function($fields) {
    if (isset($fields['billing_vat'])) {
        unset($fields['billing_vat']);
    }
    return $fields;
});

// --- Dodatkowe pola w edycji konta (Moje konto > Szczegóły konta) ---
add_action('woocommerce_edit_account_form', function() {
    $user_id = get_current_user_id();
    ?>
    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
        <label for="rodzaj_dzialalnosci"><?php _e('Rodzaj działalności', 'storefront-child'); ?></label>
        <input type="text" class="woocommerce-Input" name="rodzaj_dzialalnosci" id="rodzaj_dzialalnosci" value="<?php echo esc_attr(get_user_meta($user_id, 'rodzaj_dzialalnosci', true)); ?>" />
    </p>
    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
        <label for="company_additional_info"><?php _e('Dodatkowe informacje o firmie', 'storefront-child'); ?></label>
        <textarea class="woocommerce-Input" name="company_additional_info" id="company_additional_info"><?php echo esc_textarea(get_user_meta($user_id, 'company_additional_info', true)); ?></textarea>
    </p>
    <?php
});
add_action('woocommerce_save_account_details', function($user_id) {
    $fields = [
        'rodzaj_dzialalnosci',
        'company_additional_info'
    ];
    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            $value = $field === 'company_additional_info'
                ? sanitize_textarea_field($_POST[$field])
                : sanitize_text_field($_POST[$field]);
            update_user_meta($user_id, $field, $value);
        }
    }
});

// --- Wyświetlanie dodatkowych informacji na dashboardzie "Moje konto" ---
add_action('woocommerce_account_dashboard', function() {
    $user_id = get_current_user_id();
    $rodzaj_dzialalnosci = get_user_meta($user_id, 'rodzaj_dzialalnosci', true);
    $nip = get_user_meta($user_id, 'billing_vat', true);
    $info = get_user_meta($user_id, 'company_additional_info', true);
    if (!empty($rodzaj_dzialalnosci) || !empty($nip) || !empty($info)) {
        echo '<h3>' . __('Dodatkowe informacje o firmie', 'storefront-child') . '</h3>';
        echo '<table class="woocommerce-table shop_table">';
        if (!empty($rodzaj_dzialalnosci)) {
            echo '<tr><th>' . __('Rodzaj działalności', 'storefront-child') . '</th><td>' . esc_html($rodzaj_dzialalnosci) . '</td></tr>';
        }
        if (!empty($nip)) {
            echo '<tr><th>' . __('NIP', 'storefront-child') . '</th><td>' . esc_html($nip) . '</td></tr>';
        }
        if (!empty($info)) {
            echo '<tr><th>' . __('Dodatkowe informacje', 'storefront-child') . '</th><td>' . esc_html($info) . '</td></tr>';
        }
        echo '</table>';
    }
});
