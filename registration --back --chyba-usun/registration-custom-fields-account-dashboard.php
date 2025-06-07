<?php

/**
 * Dodaje pola z formularza rejestracji do konta klienta w WooCommerce
 */
function dodaj_pola_do_konta_klienta() {
    // Sprawdzenie, czy użytkownik jest zalogowany
    if (!is_user_logged_in()) {
        return;
    }
    
    // Pobieranie ID bieżącego użytkownika
    $user_id = get_current_user_id();
    
    // Sprawdzenie, czy użytkownik ma rolę "zainteresowany_oferta"
    $user = new WP_User($user_id);
    
    // Pobieranie danych z metadanych użytkownika
    $rodzaj_dzialalnosci = get_user_meta($user_id, 'rodzaj_dzialalnosci', true);
    $nip = get_user_meta($user_id, 'billing_vat', true);
    
    // Sprawdzenie, czy istnieją dane do wyświetlenia
    if (!empty($rodzaj_dzialalnosci) || !empty($nip)) {
        echo '<h3>' . __('Dodatkowe nformacje o firmie', 'storefront-child') . '</h3>';
        echo '<table class="woocommerce-table shop_table">';
        
        // Wyświetlanie rodzaju działalności, jeśli istnieje
        if (!empty($rodzaj_dzialalnosci)) {
            echo '<tr>';
            echo '<th>' . __('Rodzaj działalności', 'storefront-child') . '</th>';
            echo '<td>' . esc_html($rodzaj_dzialalnosci) . '</td>';
            echo '</tr>';
        }
        
        // Wyświetlanie NIP-u, jeśli istnieje
        if (!empty($nip)) {
            echo '<tr>';
            echo '<th>' . __('NIP', 'storefront-child') . '</th>';
            echo '<td>' . esc_html($nip) . '</td>';
            echo '</tr>';
        }
        
        echo '</table>';
    }
}

// Hook, który dodaje pola do konta klienta na dashboardzie WooCommerce
// add_action('woocommerce_account_dashboard', 'dodaj_pola_do_konta_klienta');
