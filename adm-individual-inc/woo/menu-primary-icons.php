<?php


/*
// Wersja dodania dodatkowego bloku z ikonami po bloku menu

function adm_output_menu_woo_icons() {
    if (!class_exists('WooCommerce')) return;

    $account_url = esc_url(get_permalink(get_option('woocommerce_myaccount_page_id')));
    $cart_url = wc_get_cart_url();
    $cart_count = WC()->cart->get_cart_contents_count();

	echo '<div class="adm--menu-woo-icons">
	<ul class="menu">
		<li class="adm-account-link-li"><a href="' . $account_url . '">
			<i class="fas fa-user"></i>
		</a></li>
		<li class="adm-cart-link-li"><a href="' . $cart_url . '">
			<i class="fas fa-shopping-cart"></i>
			<span class="cart-count">' . $cart_count . '</span>
		</a></li>
	</ul>
	</div>';

}

add_action('storefront_header', 'adm_output_menu_woo_icons', 60);


/**/


function adm_add_icons_to_menu($items, $args) {
    if ( 
        $args->theme_location === 'primary' && 
        class_exists('WooCommerce') 
    ) {


        $account_url = esc_url( get_permalink( get_option('woocommerce_myaccount_page_id') ) );

        $cart_url = wc_get_cart_url();
        $cart_count = WC()->cart->get_cart_contents_count();

        $items .= '<li class="menu-item menu-item-type-custom adm--li--menu-woo-icons">
	<div id="adm--menu-woo-icons">
            <a href="' . $account_url . '" class="adm-account-link">
                <i class="fas fa-user"></i>
            </a>
            <a href="' . esc_url($cart_url) . '" class="adm-cart-link">
                <i class="fas fa-shopping-cart"></i><span class="cart-count">' . $cart_count . '</span>
            </a>
	</div>
        </li>';

    }

    return $items;
}
add_filter('wp_nav_menu_items', 'adm_add_icons_to_menu', 10, 2);

//*/

add_action('wp_enqueue_scripts', function () {

    if (file_exists(ADM_THEME_DIR."adm-inc/css/menu-primary-woo-icons.css")) {
        wp_enqueue_style(
            'adm--woo-menu-icons',
            ADM_THEME_URI."adm-inc/css/menu-primary-woo-icons.css",
            array(),
            '1.0.0',
            'all'
        );
    }

}, 20);


