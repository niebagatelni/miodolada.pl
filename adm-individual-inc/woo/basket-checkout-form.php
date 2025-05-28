<?php




// Sprawdzenie dostępu klienta
function czy_klient_moze_kupowac() {
    if ( !is_user_logged_in() ) return false;
    $user = wp_get_current_user();
    return !in_array('zainteresowany_oferta', (array) $user->roles);
}


// Zablokowanie kupowania
add_filter('woocommerce_is_purchasable', function($can_purchase, $product) {
    return czy_klient_moze_kupowac() ? $can_purchase : false;
}, 10, 2);


// Zamiast "Dodaj do koszyka" na liście produktów – przyciski
add_filter('woocommerce_loop_add_to_cart_link', function($html, $product) {
    if ( !czy_klient_moze_kupowac() ) {
        $my_account_url = get_permalink( wc_get_page_id( 'myaccount' ) );
        return '<a href="' . esc_url($my_account_url) . '?f=logowanie" class="button">Zaloguj się</a> 
                <a href="' . esc_url($my_account_url) . '?f=rejestracja" class="button">Zarejestruj się</a>';
    }
    return $html;
}, 10, 2);


// Komunikat zamiast przycisku na stronie produktu
add_action('woocommerce_single_product_summary', function() {
    if ( !czy_klient_moze_kupowac() ) {
        remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
        $my_account_url = get_permalink( wc_get_page_id( 'myaccount' ) );
        echo '<div class="woocommerce-info">Aby kupić ten produkt, <a href="' . esc_url($my_account_url) . '">zaloguj się</a> lub 
              <a href="' . esc_url($my_account_url) . '">zarejestruj się</a>.</div>';
    }
}, 1);
