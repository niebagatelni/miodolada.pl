<?php


//echo $undefined_variable;
// trigger_error("Test błędu do logu w pliku function.php", E_USER_WARNING); // test wp-log można umieścićw function.php



if ( ! function_exists( 'define_const' ) ) {
function define_const($name, $value) {
    if ( ! defined( $name ) ) {
        define( $name, $value );
    }
}


define_const('ADM_THEME_DIR', get_stylesheet_directory()."/");
define_const('ADM_THEME_URI', get_stylesheet_directory_uri()."/");


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



// Załaduj style motywu nadrzędnego
add_action('wp_enqueue_scripts', function() {
    wp_enqueue_style(
        'adm--storefront-style',
        get_template_directory_uri() . '/style.css',
        [],
        file_exists(get_template_directory() . '/style.css') ? filemtime(get_template_directory() . '/style.css') : null,
        'all'
    );
}, 10);



// Załaduj style motywu potomnego
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style(
        'adm--storefront-child-style',
        get_stylesheet_uri(),
        ['adm--storefront-style'],
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
}, 20);


$rr = get_stylesheet_directory() . '/adm-inc/includes.php';
if (file_exists($rr)) {
	require_once $rr;
}



$rr = get_stylesheet_directory() . '/adm-individual-inc/includes.php';
if (file_exists($rr)) {
	require_once $rr;
}


// Czy treści blogowe ale nie na głównej stronie
function adm_is_blog_context() {
	return ( is_home() || is_archive() || is_category() || is_tag() || is_singular('post') ) && ! is_front_page();
}
