<?php

$fname_log = "[.]";



adm_include_in_theme('/adm-inc/includes/dane-kontaktowe.php');

adm_include_in_theme('/adm-inc/includes/custom-blocks.php');

adm_include_in_theme('/adm-inc/includes/admin-bar-shortcodes.php');

adm_include_in_theme('/adm-inc/includes/blog-layout.php');

adm_include_in_theme('/adm-inc/includes/header-layout.php');
adm_include_in_theme('/adm-inc/includes/header-customizer.php');

adm_include_in_theme('/adm-inc/includes/post-metadata.php');

adm_include_in_theme('/adm-inc/includes/color-customizer.php');



if( class_exists('WooCommerce') ){
	adm_include_in_theme('/adm-inc/woo/menu-primary-icons.php');
	adm_include_in_theme('/adm-inc/woo/customers-list.php');
    adm_include_in_theme('/adm-inc/woo/dashboard-cancel-order.php');
    adm_include_in_theme('/adm-inc/woo/dashboard-links.php');
    
}

 
// Usuwa sortowanie i zliczenie produktów w sklepie WooCommerce
function custom_remove_sorting_and_result_count() {
    remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );
    remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
    remove_action( 'woocommerce_before_shop_loop', 'storefront_sorting_wrapper', 9 );
    remove_action( 'woocommerce_before_shop_loop', 'storefront_sorting_wrapper_close', 31 );
}
add_action( 'woocommerce_before_main_content', 'custom_remove_sorting_and_result_count' );




/*

Priorytety CSS:

color-set		        : 10
header-layout		    : 22
ustawienia bloga	    : 23
custom-blocks		    : 25
color-settings-output 	: 100

*/


/*
add_action('admin_menu', 'adm_custom_menu');
function adm_custom_menu()
{
    add_menu_page('Wzorce', 'Wzorce', 'read', "/wp-admin/edit.php?post_type=wp_block", '', 'dashicons-text', 1);
}
*/






/* ---------------------------------- */
/* ------- Skróty Klawiatury -------- */


function adm__keydown_shortcuts($hook) {
    $path = 'adm-inc/js/keydown-shortcuts.js';
    if (file_exists(ADM_THEME_DIR . $path)){
        wp_enqueue_script(
            'adm--keydown-shortcuts',
            ADM_THEME_URI . $path,
            [],
            filemtime(ADM_THEME_DIR . $path),
            true
        );
    }
}
add_action('admin_enqueue_scripts', 'adm__keydown_shortcuts');




//*/



