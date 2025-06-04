<?php
/**
 * Kompletne zabezpieczenie zakupów dla niezalogowanych i roli "zainteresowany_oferta"
 */
 

// Sprawdzenie dostępu klienta
function czy_klient_moze_kupowac() {
    $user = wp_get_current_user();
    return !in_array('zainteresowany_oferta', (array) $user->roles);
}

function login_register_wp_buttons() {
    $my_account_url= get_permalink( wc_get_page_id( 'myaccount' ) );

    $html = '<div class="wp-block-buttons is-content-justification-center is-layout-flex wp-block-buttons-is-layout-flex" style="border-radius:8px">';
    
    $html .= '<div class="wp-block-button">';
    $html .= '<a class="wp-block-button__link wp-element-button" href="' . esc_url($my_account_url) . '">Zaloguj się</a>';
    $html .= '</div>';

    $html .= '<div class="wp-block-button">';
    $html .= '<a class="wp-block-button__link wp-element-button" href="' . esc_url($my_account_url) . '#customer_registration">Zarejestruj się</a>';
    $html .= '</div>';

    $html .= '</div>';

    return $html;
}


function login_register_buttons($class = 'button', $style = 'inline') {
    $my_account_url = get_permalink( wc_get_page_id( 'myaccount' ) );

	$html  =  '<p><a href="' . esc_url($my_account_url) . '" class="' . esc_attr($class) . ' login-btn">Zaloguj się</a>';
	$html .=  '<a href="' . esc_url($my_account_url) . '#customer_registration" class="' . esc_attr($class) . ' register-btn">Zarejestruj się</a></p>';

	return $html;
}


function get_offer_pending_message() {
    return 'Jeszcze nie przygotowaliśmy dla Ciebie oferty.<br>Jeśli rejestrowałeś się ponad 3 dni temu, napisz w tej sprawie na adres sklep@pachniczowka.pl';
}


function get_login_register_buttons($class = 'button', $style = 'inline') {
    $my_account_url = get_permalink(wc_get_page_id('myaccount'));
    
    if (!is_user_logged_in()) {
        if ($style === 'block') {
            return '<div class="woocommerce-info login-register-notice">
                        <p>Aby kupić ten produkt, musisz się zalogować lub zarejestrować.</p>
                        <p>
                            <a href="' . esc_url($my_account_url) . '" class="' . esc_attr($class) . ' login-btn">Zaloguj się</a>
                            <a href="' . esc_url($my_account_url) . '#customer_registration" class="' . esc_attr($class) . ' register-btn">Zarejestruj się</a>
                        </p>
                    </div>';
        } else {
            return '<a href="' . esc_url($my_account_url) . '" class="' . esc_attr($class) . ' login-btn">Zaloguj się</a> 
                    <a href="' . esc_url($my_account_url) . '#customer_registration" class="' . esc_attr($class) . ' register-btn">Zarejestruj się</a>';
        }
    } else { // Użytkownik zalogowany ale z rolą "zainteresowany_oferta"

        if ($style === 'block') {
            return '<div class="woocommerce-info offer-pending-notice">
                        <p>' . get_offer_pending_message() . '</p>
                    </div>';
        } else {
            return '<p class="offer-pending-inline">' . get_offer_pending_message() . '</p>';
        }
    }
}



add_shortcode('adm_empty_basket', function() {
 
    if ( !is_user_logged_in() ){
        $style = '<style>.wc-empty-cart-message, .woocommerce-notices-wrapper, .return-to-shop { display: none !important; }</style>';
        $msg = '<div class="woocommerce-error" style="margin-top: 20px;">';
        $msg .= 'Musisz zalogować się, aby dokonać zakupu';
        $msg .= '</div>';
        $msg .= login_register_buttons();

        return $style . $msg;
    }
    
    if ( !czy_klient_moze_kupowac() ){
        $style = '<style>.wc-empty-cart-message, .woocommerce-notices-wrapper, .return-to-shop { display: none !important; }</style>';
        $msg = '<div class="woocommerce-message" style="margin-top: 20px;">';
        $msg .= get_offer_pending_message();
        $msg .= '</div>';
        return $style . $msg;
    }

    return '';
});



// 1. BLOKOWANIE KUPOWANIA - podstawowe zabezpieczenie
add_filter('woocommerce_is_purchasable', function($can_purchase, $product) {
    return (is_user_logged_in() && czy_klient_moze_kupowac()) ? $can_purchase : false;
}, 10, 2);


// 2. LISTA PRODUKTÓW - przyciski zamiast "Dodaj do koszyka" 
// add_filter('woocommerce_loop_add_to_cart_link', function($html, $product) {
//     if ( !czy_klient_moze_kupowac()  ||  !is_user_logged_in()  ) {
//         $my_account_url = get_permalink( wc_get_page_id( 'myaccount' ) );
//         return '<a href="' . esc_url($my_account_url) . '?f=logowanie" class="button">Zaloguj się</a> 
//                 <a href="' . esc_url($my_account_url) . '?f=rejestracja" class="button">Zarejestruj się</a>';

//         // alternatywnie:
//         // return get_login_register_buttons('button add_to_cart_button');
//     }
//     return $html;
// }, 10, 2);


// 3. STRONA PRODUKTU - zamiana sekcji dodawania do koszyka
// add_action('woocommerce_single_product_summary', function() {
//     if ( !czy_klient_moze_kupowac()) {
//         remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
//     }
// }, 1);

add_action('woocommerce_single_product_summary', function() {
    if (!is_user_logged_in()) {
        echo get_login_register_buttons('button single_add_to_cart_button', 'block');
    }
}, 30);




// // 4. POZWALAMY NA DODAWANIE DO KOSZYKA - tylko informujemy o potrzebie logowania
// add_action('woocommerce_add_to_cart', function($cart_item_key, $product_id) {
//     if (!czy_klient_moze_kupowac()) {
//         wc_add_notice('Produkt został dodany do koszyka. Aby sfinalizować zakup, musisz się zalogować lub zarejestrować.', 'notice');
//     }
// }, 10, 2);



// 5. KOMUNIKAT W KOSZYKU - informacyjny, nie blokujący
add_action('woocommerce_before_cart', function() {
    if (!is_user_logged_in()) {
        echo '<div class="woocommerce-info">
                <strong>Uwaga!</strong> Aby sfinalizować zamówienie, musisz się zalogować lub zarejestrować.
                ' . get_login_register_buttons('button', 'inline') . '
              </div>';
    } elseif (!czy_klient_moze_kupowac()) {
        echo '<div class="woocommerce-info">
                <strong>Uwaga!</strong> ' . get_offer_pending_message() . '
              </div>';
    }
});


// 6. STRONA CHECKOUT - blokowanie i przekierowanie
add_action('template_redirect', function() {
    if (is_checkout()) {
        global $custom_checkout_redirect_done;

        if (!is_user_logged_in()) {
            wc_add_notice('Musisz się zalogować lub zarejestrować, aby dokonać zamówienia.', 'error');
            $custom_checkout_redirect_done = true;
            wp_safe_redirect(wc_get_cart_url());
            exit;
        } elseif (!czy_klient_moze_kupowac()) {
            wc_add_notice(get_offer_pending_message(), 'error');
            $custom_checkout_redirect_done = true;
            wp_safe_redirect(wc_get_cart_url());
            exit;
        }
    }
});


// 7. BLOKOWANIE CHECKOUT PRZEZ HOOK
add_action('woocommerce_checkout_init', function() {
    global $custom_checkout_redirect_done;
    if (!empty($custom_checkout_redirect_done)) {
        return;
    }

    if (!is_user_logged_in()) {
        wp_die('Brak uprawnień do realizacji zamówienia. <a href="' . get_permalink(wc_get_page_id('myaccount')) . '">Zaloguj się</a>');
    } elseif (!czy_klient_moze_kupowac()) {
        wp_die('Brak uprawnień do realizacji zamówienia. ' . get_offer_pending_message());
    }

});


// 8. UKRYCIE CEN
add_filter('woocommerce_get_price_html', function($price, $product) {
    if (!is_user_logged_in()) {
        return '<span class="login-to-see-price">Zaloguj się, aby zobaczyć cenę</span>';
    } elseif (!czy_klient_moze_kupowac()) {
        return '<span class="offer-pending-price">Oferta w przygotowaniu</span>';
    }
    return $price;
}, 10, 2);


// 9. BLOKOWANIE API/REST (dla zewnętrznych aplikacji)
add_filter('woocommerce_rest_check_permissions', function($permission, $context, $object_id, $post_type) {
    if ($post_type === 'shop_order' && (!is_user_logged_in() || !czy_klient_moze_kupowac())) {
        return false;
    }
    return $permission;
}, 10, 4);


// 10. BLOKOWANIE WIDGET KOSZYKA
add_filter('woocommerce_widget_cart_is_hidden', function($is_hidden) {
    if (!is_user_logged_in() || !czy_klient_moze_kupowac()) {
        return true;
    }
    return $is_hidden;
});


// 11. BLOKOWANIE QUICK VIEW (jeśli używasz takiej wtyczki)
add_filter('woocommerce_loop_add_to_cart_args', function($args, $product) {
    if (!is_user_logged_in() || !czy_klient_moze_kupowac()) {
        $args['class'] = str_replace('ajax_add_to_cart', '', $args['class']);
    }
    return $args;
}, 10, 2);


// 12. KOMUNIKATY W MINI KOSZYKU
add_filter('woocommerce_add_to_cart_fragments', function($fragments) {
    if (!is_user_logged_in()) {
        $fragments['div.widget_shopping_cart_content'] = '<div class="widget_shopping_cart_content">
            <p class="woocommerce-mini-cart__empty-message">Zaloguj się, aby dodawać produkty do koszyka.</p>
        </div>';
    } elseif (!czy_klient_moze_kupowac()) {
        $fragments['div.widget_shopping_cart_content'] = '<div class="widget_shopping_cart_content">
            <p class="woocommerce-mini-cart__empty-message">Oferta w przygotowaniu</p>
        </div>';
    }
    return $fragments;
});











// 13. STYLOWANIE CSS
add_action('wp_head', function() {
    ?>
    <style>
    .login-register-notice, .offer-pending-notice {
        text-align: center;
        padding: 20px;
        margin: 20px 0;
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 5px;
    }
    .login-btn, .register-btn {
        margin: 0 5px !important;
        padding: 10px 20px !important;
    }
    .login-btn {
        background-color: #0073aa !important;
        color: white !important;
    }
    .register-btn {
        background-color: #00a32a !important;
        color: white !important;
    }
    .login-to-see-price, .offer-pending-price {
        font-style: italic;
        color: #666;
    }
    .offer-pending-inline {
        font-style: italic;
        color: #666;
        text-align: center;
    }
   
    /* Ukryj elementy dla użytkowników bez uprawnień */
    <?php if (!is_user_logged_in() || !czy_klient_moze_kupowac()): ?>
    .woocommerce-cart-form .actions,
    .checkout-button,
    .wc-proceed-to-checkout {
        display: none !important;
    }
    <?php endif; ?>
    </style>
    <?php
});



// 14. BLOKOWANIE SHORTCODE'ÓW
add_filter('woocommerce_shortcode_products_query', function($query_args, $atts, $type) {
    if ((!is_user_logged_in() || !czy_klient_moze_kupowac()) && isset($query_args['post_type'])) {
        // Można dodać dodatkowe ograniczenia dla produktów w shortcode'ach
    }
    return $query_args;
}, 10, 3);

/*
// 15. LOG AKTYWNOŚCI (opcjonalnie - do debugowania)
add_action('init', function() {
    if (WP_DEBUG && !czy_klient_moze_kupowac() && (is_shop() || is_product() || is_cart() || is_checkout())) {
        error_log('Zablokowany dostęp dla użytkownika: ' . (is_user_logged_in() ? wp_get_current_user()->user_login : 'niezalogowany'));
    }
});

//*/



?>