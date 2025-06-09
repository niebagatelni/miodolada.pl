<?php




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
}

 

/*

Priorytety CSS:

color-set		: 10
header-layout		: 22
ustawienia bloga	: 23
custom-blocks		: 25
color-settings-output 	: 100

*/


add_action('admin_menu', 'adm_custom_menu');
function adm_custom_menu()
{
    add_menu_page('Wzorce', 'Wzorce', 'read', "/wp-admin/edit.php?post_type=wp_block", '', 'dashicons-text', 1);
}






/* ---------------------------------- */
/* ------- Skróty Klawiatury -------- */


function adm__keydown_shortcuts($hook) {
    $path = 'adm-inc/js/keydown-shortcuts.js';
    if (file_exists(ADM_THEME_DIR . $path)){
        wp_enqueue_script(
            'adm--keydown-shortcuts',
            ADM_THEME_URI . $path,
            [],
            filemtime(),
            true
        );
    }
}
add_action('admin_enqueue_scripts', 'adm__keydown_shortcuts');




//*/



