<?php

//echo $undefined_variable;
// trigger_error("Test błędu do logu w pliku function.php", E_USER_WARNING); // test wp-log można umieścićw function.php

if ( ! function_exists( 'define_const' ) ) {
function define_const($name, $value) {
    if ( ! defined( $name ) ) {
        define( $name, $value );
    }
}
}

define_const('ADM_THEME_DIR', get_stylesheet_directory()."/");
define_const('ADM_THEME_URI', get_stylesheet_directory_uri()."/");


$rr = get_stylesheet_directory() . '/adm-inc/includes.php';
if (file_exists($rr)) {
    require_once $rr;
}

$rr = get_stylesheet_directory() . '/adm-individual-inc/includes.php';
if (file_exists($rr)) {
    require_once $rr;
}


function adm_include_in_theme($rr){
    if (file_exists(ADM_THEME_DIR.$rr)) {
        require_once ADM_THEME_DIR.$rr;
    }
}




/*

// Uniwersalny hook po każdej zmianie danych użytkownika (WooCommerce + WordPress)

if (!function_exists('adm_user_data_changed_trigger')) {
function adm_user_data_changed_trigger($user_id, $old_user_data = null) {
    if (empty($user_id) || !get_userdata($user_id)) {
        // Nie ma takiego użytkownika, nie wykonuj nic
        return;
    }
    //do_action('adm_user_data_changed', $user_id, $old_user_data);

    if (!function_exists('')) {
        
    }

}}

add_action('user_register', 'adm_user_data_changed_trigger', 10, 1);
add_action('profile_update', 'adm_user_data_changed_trigger', 10, 2);
add_action('edit_user_profile_update', 'adm_user_data_changed_trigger', 10, 2);
add_action('personal_options_update', 'adm_user_data_changed_trigger', 10, 2);
add_action('set_user_role', 'adm_user_data_changed_trigger', 10, 2);
add_action('add_user_role', 'adm_user_data_changed_trigger', 10, 2);
add_action('remove_user_role', 'adm_user_data_changed_trigger', 10, 2);
add_action('woocommerce_update_customer', 'adm_user_data_changed_trigger', 10, 1);
add_action('woocommerce_save_account_details', 'adm_user_data_changed_trigger', 10, 1);



*/




// Załaduj style motywu nadrzędnego
add_action('wp_enqueue_scripts', function() {
    wp_enqueue_style(
        'storefront-style',
        get_template_directory_uri() . '/style.css',
        [],
        file_exists(get_template_directory() . '/style.css') ? filemtime(get_template_directory() . '/style.css') : null,
        'all'
    );
}, 10);



// Załaduj style motywu potomnego
add_action('wp_enqueue_scripts', function () {
	
	// Najpierw usuń automatycznie załadowany styl potomny
    wp_dequeue_style('storefront-child-style');
    wp_deregister_style('storefront-child-style');

        $child_style_name = 'adm--storefront-child';
	
    // Child style
    wp_enqueue_style(
        $child_style_name,
        get_stylesheet_uri(),
        ['storefront-style'], // Standardowa nazwa handle dla Storefront
        file_exists(get_stylesheet_directory() . '/style.css') ? filemtime(get_stylesheet_directory() . '/style.css') : '1.0.0'
    );

	
    // WooCommerce style
    $woo_style_path = get_stylesheet_directory() . '/style-woo.css';
    if (class_exists('WooCommerce') && file_exists($woo_style_path)) {
        wp_enqueue_style(
            'adm--storefront-child-woocommerce',
            get_stylesheet_directory_uri() . '/style-woo.css',
            [$child_style_name],
            filemtime($woo_style_path)
        );
    }

    // Individual style
    $individual_style_path = get_stylesheet_directory() . '/style-individual.css';
    if (file_exists($individual_style_path)) {
        wp_enqueue_style(
            'adm--storefront-child-individual',
            get_stylesheet_directory_uri() . '/style-individual.css',
            [$child_style_name],
            filemtime($individual_style_path)
        );
    }


/*
    // checkout style
    $checkout_style_path = get_stylesheet_directory() . '/checkout.css';
    if (file_exists($checkout_style_path)) {
        wp_enqueue_style(
            'storefront-child-checkout',
            get_stylesheet_directory_uri() . '/checkout.css',
            ['storefront-child-style'],
            filemtime($checkout_style_path)
        );
    }
*/

}, 100);









// -------------------------------------------------------------------
// ---------------   FUNKCJE   ---------------------------------------





// Sprawdzenie dostępu klienta
if(!function_exists('czy_klient_moze_kupowac')){
    function czy_klient_moze_kupowac() {
        $user = wp_get_current_user();
        if (!$user || empty($user->roles)) {
            if (function_exists('adm_log3')) adm_log3('Brak ról lub obiektu użytkownika w czy_klient_moze_kupowac()');
            return false;
        }
        return !in_array('zainteresowany_oferta', (array) $user->roles);
    }}
    
    


// Remove created by Storefront
add_action( 'wp', 'ql_remove_credits_storefront' );
function  ql_remove_credits_storefront() {
   remove_action( 'storefront_footer', 'storefront_credit', 20 );
}



if ( ! function_exists( 'adm__get_wp_error' ) ) {
    function adm__get_wp_error( $wp_error ) {
        if ( ! is_wp_error( $wp_error ) ) {
            return '';
        }

        $error_msgs = $wp_error->get_error_messages();

        if ( empty( $error_msgs) ) {
            return 'Nieznany błąd.';
        }

        return implode( "\n", array_map( function( $msg ) {
            return 'Błąd: ' . $msg;
        }, $error_msgs ) );
    }
}






// Czy treści blogowe ale nie na głównej stronie
function adm_is_blog_context() {
    return ( is_home() || is_archive() || is_category() || is_tag() || is_singular('post') ) && ! is_front_page();
}



// Ukrywaj co trzeba zależnie czy zalogowany
if(!function_exists('czy_zalogowany_klient_hurtowy')){
function czy_zalogowany_klient_hurtowy() {
    if (is_user_logged_in()) {
        $user = wp_get_current_user();
        if (in_array('klient_hurtowy', $user->roles)) {
            return true;
        }
    }
    return false;
}}


function widocznosc_dla_hurtowego() {
    if( czy_zalogowany_klient_hurtowy() ){
        echo '<style>.klient-hurtowy-logged { display: block !important; } .klient-hurtowy-not-logged { display: none !important; }</style>';
    } else {
        echo '<style>.klient-hurtowy-logged { display: none !important; } .klient-hurtowy-not-logged { display: block !important; }</style>';
    }
}
add_action('wp_head', 'widocznosc_dla_hurtowego');



add_action('wp_head', function(){
echo'
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Chela+One&family=DynaPuff:wght@400..700&family=Gabarito:wght@400..900&family=Heebo:wght@100..900&family=Mogra&family=Rubik+Spray+Paint&family=Rum+Raisin&display=swap" rel="stylesheet">
';
});


/*

// Załaduj style motywu potomnego
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style(
        'adm--storefront-child-style',
        get_stylesheet_uri(),
        ['adm--storefront-style', 'storefront-woocommerce-brands-style-css'],
        file_exists(get_stylesheet_directory() . '/style.css') ? filemtime(get_stylesheet_directory() . '/style.css') : null,
        'all'
    );

    $woo_style_path = get_stylesheet_directory() . '/style-woo.css';
    if (class_exists('WooCommerce') && file_exists($woo_style_path)) {
        wp_enqueue_style(
            'adm--storefront-child-woocommerce',
            get_stylesheet_directory_uri() . '/style-woo.css',
            ['adm--storefront-child-style'],
            filemtime($woo_style_path),
            'all'
        );
    }

    $individual_style_path = get_stylesheet_directory() . '/style-individual.css';
    if (file_exists($individual_style_path)) {
        wp_enqueue_style(
            'adm--storefront-child-individual',
            get_stylesheet_directory_uri() . '/style-individual.css',
            ['adm--storefront-child-style'],
            filemtime($individual_style_path),
            'all'
        );
    }
}, 100);

*/


























// Dodaj metabox na główną kokpitu admina
add_action('wp_dashboard_setup', function() {
    wp_add_dashboard_widget(
        'user_szczegoly_widget',
        'Szczegóły user',
        'wyswietl_szczegoly_user_dashboard'
    );
});

function wyswietl_szczegoly_user_dashboard() {


    $all_meta = get_user_meta(106);
    foreach ($all_meta as $key => $values) {
        echo $key . ' => ' . implode(', ', $values) . "<BR>";
    }


}





// Dodaj metabox z zamówieniami na stronę główną kokpitu admina
add_action('wp_dashboard_setup', function() {
    wp_add_dashboard_widget(
        'zamowienia_szczegoly_widget',
        'Szczegóły zamówień #618, 619, 632, 633',
        'wyswietl_szczegoly_zamowien_dashboard'
    );
});

function wyswietl_szczegoly_zamowien_dashboard() {
    if (!current_user_can('manage_woocommerce')) {
        echo 'Brak dostępu.';
        return;
    }

    $order_ids = [618, 619, 632, 633];

    echo '<div style="max-height: 500px; overflow-y: auto;">';

    foreach ($order_ids as $order_id) {
        $order = wc_get_order($order_id);
        echo "<br><br><HR><HR>";

        echo "<h3>#{$order->get_id()} (Status: {$order->get_status()})</h3>";

        echo '<strong>Klient:</strong> ' . esc_html($order->get_billing_first_name() . ' ' . $order->get_billing_last_name()) . '<br>';
        echo '<strong>Klient ID:</strong> ' . $order->get_customer_id() . '<br>';
        echo '<strong>Email:</strong> ' . esc_html($order->get_billing_email()) . '<br>';
        echo '<strong>Telefon:</strong> ' . esc_html($order->get_billing_phone()) . '<br>';
        echo '<strong>Data zamówienia:</strong> ' . esc_html($order->get_date_created()->date('Y-m-d H:i:s')) . '<br>';

        echo "<HR>";
        foreach ($order->get_meta_data() as $meta) {
            echo $meta->key . ': ' . $meta->value . "<br>";
        }
        echo "<HR>";


        echo '$order->get_id() : ' . $order->get_id() . "<br>";
        echo '$order->get_status() : ' . $order->get_status() . "<br>";
        echo '$order->get_date_created() : ' . $order->get_date_created()->date('Y-m-d H:i:s') . "<br>";
        echo '$order->get_date_modified() : ' . $order->get_date_modified()->date('Y-m-d H:i:s') . "<br>";
        echo '$order->get_total() : ' . $order->get_total() . "<br>";
        echo '$order->get_total_tax() : ' . $order->get_total_tax() . "<br>";
        echo '$order->get_subtotal() : ' . $order->get_subtotal() . "<br>";
        echo '$order->get_payment_method() : ' . $order->get_payment_method() . "<br>";
        echo '$order->get_payment_method_title() : ' . $order->get_payment_method_title() . "<br>";
        echo '$order->get_shipping_method() : ' . $order->get_shipping_method() . "<br>";
        echo '$order->get_customer_id() : ' . $order->get_customer_id() . "<br>";
        echo '$order->get_billing_first_name() : ' . $order->get_billing_first_name() . "<br>";
        echo '$order->get_billing_last_name() : ' . $order->get_billing_last_name() . "<br>";
        echo '$order->get_billing_email() : ' . $order->get_billing_email() . "<br>";
        echo '$order->get_billing_phone() : ' . $order->get_billing_phone() . "<br>";
        echo '$order->get_billing_address_1() : ' . $order->get_billing_address_1() . "<br>";
        echo '$order->get_billing_address_2() : ' . $order->get_billing_address_2() . "<br>";
        echo '$order->get_billing_city() : ' . $order->get_billing_city() . "<br>";
        echo '$order->get_billing_postcode() : ' . $order->get_billing_postcode() . "<br>";
        echo '$order->get_billing_country() : ' . $order->get_billing_country() . "<br>";
        echo '$order->get_shipping_first_name() : ' . $order->get_shipping_first_name() . "<br>";
        echo '$order->get_shipping_last_name() : ' . $order->get_shipping_last_name() . "<br>";
        echo '$order->get_shipping_address_1() : ' . $order->get_shipping_address_1() . "<br>";
        echo '$order->get_shipping_address_2() : ' . $order->get_shipping_address_2() . "<br>";
        echo '$order->get_shipping_city() : ' . $order->get_shipping_city() . "<br>";
        echo '$order->get_shipping_postcode() : ' . $order->get_shipping_postcode() . "<br>";
        echo '$order->get_shipping_country() : ' . $order->get_shipping_country() . "<br>";
        echo '$order->get_coupon_codes() : ' . implode(', ', $order->get_coupon_codes()) . "<br>";
        echo '$order->get_items() : ';
        // Tu trzeba iterować po elementach, bo to tablica obiektów
        foreach ( $order->get_items() as $item_id => $item ) {
            echo "<br>  Produkt: " . $item->get_name() . ", ilość: " . $item->get_quantity() . ", cena: " . $item->get_total();
        }
        echo "<br>";
        echo '$order->get_customer_note() : ' . $order->get_customer_note() . "<br>";
        echo '$order->get_date_paid() : ';
        echo $order->get_date_paid() ? $order->get_date_paid()->date('Y-m-d H:i:s') : 'brak' . "<br>";
        echo '$order->get_date_completed() : ';
        echo $order->get_date_completed() ? $order->get_date_completed()->date('Y-m-d H:i:s') : 'brak' . "<br>";


/*
        if ($order->get_shipping_address_1()) {
            echo '<strong>Adres wysyłki:</strong><br>';
            echo esc_html($order->get_shipping_address_1()) . '<br>';
            if ($order->get_shipping_address_2()) echo esc_html($order->get_shipping_address_2()) . '<br>';
            echo esc_html($order->get_shipping_city()) . ', ' . esc_html($order->get_shipping_postcode()) . '<br>';
            echo esc_html($order->get_shipping_country()) . '<br>';
        }

        echo '<strong>Pozycje zamówienia:</strong><ul>';
        foreach ($order->get_items() as $item) {
            $product_name = $item->get_name();
            $qty = $item->get_quantity();
            $total = wc_price($item->get_total());
            echo "<li>{$product_name} x {$qty} — {$total}</li>";
        }
        echo '</ul>';

        echo '<strong>Razem:</strong> ' . $order->get_formatted_order_total() . '<hr>';
  */
  
        }

    echo '</div>';
}


