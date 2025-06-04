<?php



/**
 * Dodaje pola z formularza rejestracji do konta klienta w WooCommerce
 */
function dodaj_pola_do_konta_klienta() {
    $user_id = get_current_user_id();
    
    // Sprawdź czy użytkownik ma rolę "zainteresowany_oferta"
    $user = new WP_User($user_id);
    if (!in_array('zainteresowany_oferta', $user->roles)) {
        return;
    }
    
    $rodzaj_dzialalnosci = get_user_meta($user_id, 'rodzaj_dzialalnosci', true);
    $nip = get_user_meta($user_id, 'billing_vat', true);
    
    if (!empty($rodzaj_dzialalnosci) || !empty($nip)) {
        echo '<h3>' . __('Informacje o firmie', 'storefront-child') . '</h3>';
        echo '<table class="woocommerce-table shop_table">';
        
        if (!empty($rodzaj_dzialalnosci)) {
            echo '<tr>';
            echo '<th>' . __('Rodzaj działalności', 'storefront-child') . '</th>';
            echo '<td>' . esc_html($rodzaj_dzialalnosci) . '</td>';
            echo '</tr>';
        }
        
        if (!empty($nip)) {
            echo '<tr>';
            echo '<th>' . __('NIP', 'storefront-child') . '</th>';
            echo '<td>' . esc_html($nip) . '</td>';
            echo '</tr>';
        }
        
        echo '</table>';
    }
}

add_action('woocommerce_account_dashboard', 'dodaj_pola_do_konta_klienta');
