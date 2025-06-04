<?php

function adm_customizer_add_heading($id, $title, $wp_customize) {
$wp_customize->add_setting($id, [
    'sanitize_callback' => 'esc_html',
]);

$wp_customize->add_control($id, [
    'section' => 'adm__color_settings',
    'type' => 'hidden',
    'description' => '<h2 style="margin-top:20px; text-align: center; font-style: normal;">'.$title.'</h2>',
]);

}



function adm__sanitize_custom_css($input) {
    return wp_kses($input, []);
}

function adm__customizer_register( $wp_customize ) {

    // Jedna wspólna sekcja
    $wp_customize->add_section('adm__color_settings', [
        'title' => 'Ustawienia kolorów',
        'priority' => 30,
    ]);


    // Header

	adm_customizer_add_heading("adm__title_header", "Nagłówek strony", $wp_customize);

    $header_fields = [
        'title_color' 		=> 'Kolor tytułu',
        'subtitle_color' 	=> 'Kolor podtytułu',
	'bg' 			=> 'Tło nagłówka',
        'menu_link_color' 	=> 'Kolor linków',
        'menu_link_hover' 	=> 'Link hover',
        'menu_link_active'	=> 'Link active',
        'menu_bg' 		=> 'Tło menu',
    ];

    foreach ($header_fields as $key => $label) {
        $setting_id = "adm__header_{$key}";
        $wp_customize->add_setting($setting_id, [
            'default' => '',
            'sanitize_callback' => 'sanitize_hex_color',
            'transport' => 'postMessage',

        ]);
        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, $setting_id, [
            'label' => $label,
            'section' => 'adm__color_settings',
            'settings' => $setting_id,
        ]));
    }

    // Custom CSS dla nagłówka
    $wp_customize->add_setting('adm__header_custom_css', [
        'default' => '',
        'sanitize_callback' => 'adm__sanitize_custom_css',
         'transport' => 'postMessage',

    ]);
    $wp_customize->add_control('adm__header_custom_css', [
        'type' => 'textarea',
        'label' => 'Dodatkowy CSS',
        'section' => 'adm__color_settings',
    ]);




    // Content

	adm_customizer_add_heading("adm__title_content", "Treść", $wp_customize);

    $content_fields = [
        'text_color' 		=> 'Kolor tekstu',
        'link_color' 		=> 'Kolor linków',
        'link_hover' 		=> 'Link hover',
        'link_active' 		=> 'Link active',
        'button_text_color' 	=> 'Przycisk - tekst',
        'button_bg_color' 	=> 'Przycisk - tło',
        'button_text_hover' 	=> 'Przycisk hover - tekst',
        'button_bg_hover' 	=> 'Przycisk hover - tło',
    ];

    foreach ($content_fields as $key => $label) {
        $setting_id = "adm__content_{$key}";
        $wp_customize->add_setting($setting_id, [
            'default' => '',
            'sanitize_callback' => 'sanitize_hex_color',
            'transport' => 'postMessage',

        ]);
        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, $setting_id, [
            'label' => $label,
            'section' => 'adm__color_settings',
            'settings' => $setting_id,
        ]));
    }

    $wp_customize->add_setting('adm__content_custom_css', [
        'default' => '',
        'sanitize_callback' => 'adm__sanitize_custom_css',
            'transport' => 'postMessage',
    ]);
    $wp_customize->add_control('adm__content_custom_css', [
        'type' => 'textarea',
        'label' => 'Dodatkowy CSS do Treści',
        'section' => 'adm__color_settings',
    ]);




    // Footer

	adm_customizer_add_heading("adm__title_footer", "Stopka", $wp_customize);

    $footer_fields = [
        'bg' => 'Tło',
        'heading_color' 	=> 'Kolor nagłówków',
        'text_color' 		=> 'Kolor tekstu',
        'link_color' 		=> 'Kolor linków',
        'link_hover' 		=> 'Link hover',
        'link_active' 		=> 'Link active',
    ];

    foreach ($footer_fields as $key => $label) {
        $setting_id = "adm__footer_{$key}";
        $wp_customize->add_setting($setting_id, [
            'default' => '',
            'sanitize_callback' => 'sanitize_hex_color',
             'transport' => 'postMessage',
       ]);
        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, $setting_id, [
            'label' => $label,
            'section' => 'adm__color_settings',
            'settings' => $setting_id,
        ]));
    }
}
add_action('customize_register', 'adm__customizer_register');





function adm__output_custom_variables() {
    $css = ':root {' . PHP_EOL;

    $map = [
        // Header
        'adm__header_bg' => '--adm--header-bg-color',
        'adm__header_title_color' => '--adm--header-title-color',
        'adm__header_subtitle_color' => '--adm--header-subtitle-color',
        'adm__header_text_color' => '--adm--header-text-color',
        'adm__header_menu_bg' => '--adm--header-menu-bg-color',
        'adm__header_menu_link_color' => '--adm--header-menu-link-color',
        'adm__header_menu_link_hover' => '--adm--header-menu-link-hover-color',
        'adm__header_menu_link_active' => '--adm--header-menu-link-active-color',

        // Content
        'adm__content_text_color' => '--adm--content-text-color',
        'adm__content_link_color' => '--adm--content-link-color',
        'adm__content_link_hover' => '--adm--content-link-hover-color',
        'adm__content_link_active' => '--adm--content-link-active-color',
        'adm__content_button_text_color' => '--adm--button-text-color',
        'adm__content_button_bg_color' => '--adm--button-bg-color',
        'adm__content_button_text_hover' => '--adm--button-active-text-color',
        'adm__content_button_bg_hover' => '--adm--button-active-bg-color',

        // Footer
        'adm__footer_bg' => '--adm--footer-bg-color',
        'adm__footer_heading_color' => '--adm--footer-heading-color',
        'adm__footer_text_color' => '--adm--footer-text-color',
        'adm__footer_link_color' => '--adm--footer-link-color',
        'adm__footer_link_hover' => '--adm--footer-link-hover-color',
        'adm__footer_link_active' => '--adm--footer-link-active-color',
    ];

    foreach ($map as $setting => $var) {
        $val = get_theme_mod($setting);
        if ($val) {
            $css .= "  {$var}: {$val};" . PHP_EOL;
        }
    }

    $css .= '}' . PHP_EOL;

    // Dodatkowy CSS z trzech sekcji
    $custom_css = implode("\n", [
        get_theme_mod('adm__header_custom_css', ''),
        get_theme_mod('adm__content_custom_css', ''),
        get_theme_mod('adm__footer_custom_css', ''),
    ]);

    echo '<style id="adm--color-settings-vars">' . PHP_EOL;
    echo $css;
    echo $custom_css;
    echo PHP_EOL . '</style>';
}
add_action('wp_head', 'adm__output_custom_variables', 10);

// add_action('storefront_header_styles', 'adm__output_custom_variables', 10);






add_action('customize_controls_enqueue_scripts', function () {
    wp_enqueue_style(
        'adm--color-settings-customizer',
        get_theme_file_uri() . '/adm-inc/css/color-customizer.css',
        [],
        '1.0.1'
    );
});







/* ---------------------------------- */
/* ------- Usunięcie ustawień kolorów z ustawien motywu (customizera) -------- */



function adm_late_remove_storefront_color_settings( $wp_customize ) {
    if ( ! isset( $wp_customize ) ) return;

    // Nagłówek (header)
    if ( $wp_customize->get_setting( 'storefront_header_background_color' ) ) {
        $wp_customize->remove_control( 'storefront_header_background_color' );
        $wp_customize->remove_setting( 'storefront_header_background_color' );
    }

    if ( $wp_customize->get_setting( 'storefront_header_text_color' ) ) {
        $wp_customize->remove_control( 'storefront_header_text_color' );
        $wp_customize->remove_setting( 'storefront_header_text_color' );
    }

    if ( $wp_customize->get_setting( 'storefront_header_link_color' ) ) {
        $wp_customize->remove_control( 'storefront_header_link_color' );
        $wp_customize->remove_setting( 'storefront_header_link_color' );
    }

    // Sekcja "Tło" (background)
    if ( $wp_customize->get_control( 'background_color' ) ) {
        $wp_customize->remove_control( 'background_color' );
        $wp_customize->remove_setting( 'background_color' );
    }

    if ( $wp_customize->get_control( 'background_image' ) ) {
        $wp_customize->remove_control( 'background_image' );
        $wp_customize->remove_setting( 'background_image' );
    }

    if ( $wp_customize->get_section( 'background_image' ) ) {
        $wp_customize->remove_section( 'background_image' );
    }

    // Sekcja "Stopka" (footer)
    if ( $wp_customize->get_section( 'storefront_footer' ) ) {
        $wp_customize->remove_section( 'storefront_footer' );
    }

    $footer_settings = [
        'storefront_footer_background_color',
        'storefront_footer_link_color',
        'storefront_footer_text_color',
    ];
    foreach ( $footer_settings as $setting ) {
        if ( $wp_customize->get_setting( $setting ) ) {
            $wp_customize->remove_control( $setting );
            $wp_customize->remove_setting( $setting );
        }
    }

    // Sekcja "Przyciski"
    if ( $wp_customize->get_section( 'storefront_buttons' ) ) {
        $wp_customize->remove_section( 'storefront_buttons' );
    }

    $button_settings = [
        'storefront_button_background_color',
        'storefront_button_text_color',
        'storefront_button_alt_background_color',
        'storefront_button_alt_text_color',
    ];
    foreach ( $button_settings as $setting ) {
        if ( $wp_customize->get_setting( $setting ) ) {
            $wp_customize->remove_control( $setting );
            $wp_customize->remove_setting( $setting );
        }
    }

    // Sekcja "Typografia"
    if ( $wp_customize->get_section( 'storefront_typography' ) ) {
        $wp_customize->remove_section( 'storefront_typography' );
    }

    $typography_settings = [
        'storefront_heading_color',
        'storefront_text_color',
    ];
    foreach ( $typography_settings as $setting ) {
        if ( $wp_customize->get_setting( $setting ) ) {
            $wp_customize->remove_control( $setting );
            $wp_customize->remove_setting( $setting );
        }
    }


	if ( $wp_customize->get_section( 'storefront_more' ) ) {
	    $wp_customize->remove_section( 'storefront_more' );
	}
	
	$more_settings = [
	    'storefront_heading_font_family',
	    'storefront_body_font_family',
	];
	foreach ( $more_settings as $setting ) {
	    if ( $wp_customize->get_setting( $setting ) ) {
	        $wp_customize->remove_control( $setting );
	        $wp_customize->remove_setting( $setting );
	    }
	}


}

add_action( 'customize_register', 'adm_late_remove_storefront_color_settings', 100 );






/* ---------------------------------------------------------- */
/* ------- Kolory z customizera `Ustawienia kolorów` -------- */

add_action('wp_enqueue_scripts', function() {
    wp_enqueue_style(
        'adm--color-settings-output',
        get_theme_file_uri() . '/adm-inc/css/color-customizer-output.css',
	[],
	filemtime( get_theme_file_path() . '/adm-inc/css/color-customizer-output.css'),
	'all'
    );
}, 100);


function adm_enqueue_customizer_js() {
    wp_enqueue_script(
        'adm-customizer',
        get_theme_file_uri() . '/adm-inc/includes/customizer/customizer.js',
        array('jquery', 'wp-customize-preview'),
        null,
        true
    );
}
add_action('customize_preview_init', 'adm_enqueue_customizer_js');






