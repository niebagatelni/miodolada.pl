<?php



define('ADM_THEME_DIR', get_stylesheet_directory()."/");
define('ADM_THEME_URI', get_stylesheet_directory_uri()."/");

function adm_include_in_theme($rr){
	if (file_exists(ADM_THEME_DIR.$rr)) {
	    require_once ADM_THEME_DIR.$rr;
	}
}



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




if ( !function_exists( 'generate_password_reset_link' ) ) {
function generate_password_reset_link($email) {
    if ( ! email_exists( $email ) ) {
        return false;
    }

    $user = get_user_by( 'email', $email );

    if ( ! $user || is_wp_error( $user ) ) {
        return false;
    }

    $reset_key = get_password_reset_key( $user );

    if ( is_wp_error( $reset_key ) ) {
        return false;
    }

    // Budujemy pełny link resetu hasła
       $reset_url = add_query_arg(
            array(
                'key' => $reset_key,
                'id'  => $user->ID,
            ),
            wc_get_endpoint_url( 'lost-password', '', wc_get_page_permalink( 'myaccount' ) )
        );

    	return $reset_url;
}}






add_action('wp_enqueue_scripts', function(){
    wp_enqueue_style(
        'storefront-style',
        get_template_directory_uri() . '/style.css',
        [],
        file_exists(get_template_directory() . '/style.css') ? filemtime(get_template_directory() . '/style.css') : null,
        'all'
    );
}
 , 10);


add_action('wp_enqueue_scripts', function () {
/*    wp_enqueue_style(
        'adm--storefront-child-style',
        get_stylesheet_uri(),
        [],
        file_exists(get_stylesheet_directory() . '/style.css') ? filemtime(get_stylesheet_directory() . '/style.css') : null,
        'all'
    );
*/
    $woo_style_path = get_stylesheet_directory() . '/style-woo.css';
    if (class_exists('WooCommerce') && file_exists($woo_style_path)) {
        wp_enqueue_style(
            'storefront-child-woocommerce',
            get_stylesheet_directory_uri() . '/style-woo.css',
            [],
            filemtime($woo_style_path),
            'all'
        );
    }
}, 20);





$rr = get_stylesheet_directory() . '/adm-inc/includes.php';
if (file_exists($rr)) {
	require_once $rr;
}



$rr = get_stylesheet_directory() . '/adm-individual-inc/includes.php';
if (file_exists($rr)) {
	require_once $rr;
}



function adm_is_blog_context() {
	return ( is_home() || is_archive() || is_category() || is_tag() || is_singular('post') ) && ! is_front_page();
}








// Wysyłka liczona po liczbie produktw
// 

// 1. Dodaj pole "Koszt dostawy za sztukę w edycji produktu (panel admina)
add_action('woocommerce_product_options_general_product_data', function () {
    woocommerce_wp_text_input([
        'id' => '_delivery_cost_per_item',
        'label' => 'Koszt dostawy za sztukę (zł)',
        'type' => 'number',
        'custom_attributes' => [
            'step' => '0.01',
            'min' => '0'
        ],
        'desc_tip' => true,
        'description' => 'Ustaw koszt dostawy za jedną sztukę tego produktu. Wysyłka zostanie obliczona jako suma: ilosc koszt.'
    ]);
});

// 2. Zapisz wartość pola po zapisaniu produktu
add_action('woocommerce_process_product_meta', function ($post_id) {
    if (isset($_POST['_delivery_cost_per_item'])) {
        update_post_meta($post_id, '_delivery_cost_per_item', wc_clean($_POST['_delivery_cost_per_item']));
    }
});

// 3. Nadpisz koszt wysyłki dla metody "Kurier" (np. flat_rate:2)
add_filter('woocommerce_package_rates', 'custom_shipping_cost_for_kurier', 20, 2);

function custom_shipping_cost_for_kurier($rates, $package) {
    $target_method_id = 'flat_rate:2'; // <-- Upewnij się, źe to ID Twojej metody "Kurier"

    $custom_shipping_cost = 0;

    foreach ($package['contents'] as $item) {
        $product_id = $item['product_id'];
        $qty = $item['quantity'];
        $cost_per_item = get_post_meta($product_id, '_delivery_cost_per_item', true);

        if ($cost_per_item !== '' && is_numeric($cost_per_item)) {
            $custom_shipping_cost += floatval($cost_per_item) * $qty;
        }
    }

    // Nadpisz koszt tylko dla metody "Kurier"
    foreach ($rates as $rate_id => $rate) {
        if ($rate_id === $target_method_id) {
            $rates[$rate_id]->cost = $custom_shipping_cost;

            // Wyzeruj podatki (jesli nie nie uzywasz stawek VAT do wysylki)
            if (!empty($rates[$rate_id]->taxes) && !is_null($rates[$rate_id]->taxes) ) {
                foreach ($rates[$rate_id]->taxes as $key => $tax) {
                    // $rates[$rate_id]->taxes[$key] = 0;
                }
            }
        }
    }

    return $rates;
}

