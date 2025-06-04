<?php

function adm_header_customize($wp_customize) {

    // ======================
    // STRONA GŁÓWNA (Home)
    // ======================

    // Układ nagłówka - Strona główna
    $wp_customize->add_setting('header_layout_home', [
        'default'           => 'layout1',
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    $wp_customize->add_control('header_layout_home_control', [
        'label'    => __('Układ nagłówka (Strona główna)', 'mytheme'),
        'section'  => 'header_image',
        'settings' => 'header_layout_home',
        'type'     => 'select',
        'choices'  => [
            'layout1' => __('Paralaxa', 'header-uklad-1'),
            'layout2' => __('W jednej linii', 'header-uklad-2'),
            'layout3' => __('Do środka', 'header-uklad-3'),
        ],
    ]);

    $wp_customize->add_setting('hide_logo_home', [
        'default'           => false,
        'sanitize_callback' => 'wp_validate_boolean',
    ]);
    $wp_customize->add_control('hide_logo_home_control', [
        'label'    => __('Ukryj logo (Strona główna)', 'mytheme'),
        'section'  => 'header_image',
        'settings' => 'hide_logo_home',
        'type'     => 'checkbox',
    ]);

    $wp_customize->add_setting('hide_site_title_home', [
        'default'           => false,
        'sanitize_callback' => 'wp_validate_boolean',
    ]);
    $wp_customize->add_control('hide_site_title_home_control', [
        'label'    => __('Ukryj tytuł witryny (Strona główna)', 'mytheme'),
        'section'  => 'header_image',
        'settings' => 'hide_site_title_home',
        'type'     => 'checkbox',
    ]);

    $wp_customize->add_setting('hide_site_tagline_home', [
        'default'           => false,
        'sanitize_callback' => 'wp_validate_boolean',
    ]);
    $wp_customize->add_control('hide_site_tagline_home_control', [
        'label'    => __('Ukryj slogan witryny (Strona główna)', 'mytheme'),
        'section'  => 'header_image',
        'settings' => 'hide_site_tagline_home',
        'type'     => 'checkbox',
    ]);


    $wp_customize->add_setting('site_header_style_home', [
        'default'           => true,
        'sanitize_callback' => 'wp_validate_boolean',
    ]);
    $wp_customize->add_control('site_header_style_home_control', [
        'label'    => __('Logo i tytuły w jednej linii?', 'mytheme'),
        'section'  => 'header_image',
        'settings' => 'site_header_style_home',
        'type'     => 'checkbox',
    ]);
	
	
	
    // ======================
    // POZOSTAŁE STRONY
    // ======================

    // Układ nagłówka - Inne strony
    $wp_customize->add_setting('header_layout_other', [
        'default'           => 'layout1',
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    $wp_customize->add_control('header_layout_other_control', [
        'label'    => __('Układ nagłówka (Pozostałe strony)', 'mytheme'),
        'section'  => 'header_image',
        'settings' => 'header_layout_other',
        'type'     => 'select',
        'choices'  => [
            'layout1' => __('Paralaxa', 'header-uklad-1'),
            'layout2' => __('W jednej linii', 'header-uklad-2'),
            'layout3' => __('Do środka', 'header-uklad-3'),
        ],
    ]);

    $wp_customize->add_setting('hide_logo_other', [
        'default'           => false,
        'sanitize_callback' => 'wp_validate_boolean',
    ]);
    $wp_customize->add_control('hide_logo_other_control', [
        'label'    => __('Ukryj logo (Pozostałe strony)', 'mytheme'),
        'section'  => 'header_image',
        'settings' => 'hide_logo_other',
        'type'     => 'checkbox',
    ]);

    $wp_customize->add_setting('hide_site_title_other', [
        'default'           => false,
        'sanitize_callback' => 'wp_validate_boolean',
    ]);
    $wp_customize->add_control('hide_site_title_other_control', [
        'label'    => __('Ukryj tytuł witryny (Pozostałe strony)', 'mytheme'),
        'section'  => 'header_image',
        'settings' => 'hide_site_title_other',
        'type'     => 'checkbox',
    ]);

    $wp_customize->add_setting('hide_site_tagline_other', [
        'default'           => false,
        'sanitize_callback' => 'wp_validate_boolean',
    ]);
    $wp_customize->add_control('hide_site_tagline_other_control', [
        'label'    => __('Ukryj slogan witryny (Pozostałe strony)', 'mytheme'),
        'section'  => 'header_image',
        'settings' => 'hide_site_tagline_other',
        'type'     => 'checkbox',
    ]);


    $wp_customize->add_setting('site_header_style_other', [
        'default'           => true,
        'sanitize_callback' => 'wp_validate_boolean',
    ]);
    $wp_customize->add_control('site_header_style_other_control', [
        'label'    => __('Logo i tytuły w jednej linii?', 'mytheme'),
        'section'  => 'header_image',
        'settings' => 'site_header_style_other',
        'type'     => 'checkbox',
    ]);

}
add_action('customize_register', 'adm_header_customize');

// ======================
// ŁADOWANIE CSS
// ======================

add_action('wp_enqueue_scripts', function () {

    $layout = is_front_page()
        ? get_theme_mod('header_layout_home')
        : get_theme_mod('header_layout_other');

    // Mapa layoutów do plików CSS
    $layout_css_map = [
        'layout1' => [
            'front' => '/adm-inc/css/header-layout-paralaxa-home.css',
            'blog'  => '/adm-inc/css/header-layout-paralaxa-blog.css',
            'other' => '/adm-inc/css/header-layout-paralaxa-other.css',
        ],
        'layout2' => [
            'front' => '/adm-inc/css/header-layout-inline-home.css',
            'blog'  => '/adm-inc/css/header-layout-inline-blog.css',
            'other' => '/adm-inc/css/header-layout-inline-other.css',
        ],
        'layout3' => [
            'front' => '/adm-inc/css/header-layout-centered-home.css',
            'blog'  => '/adm-inc/css/header-layout-centered-blog.css',
            'other' => '/adm-inc/css/header-layout-centered-other.css',
        ]
    ];

    // Ustaw domyślny layout na layout1, jeśli coś nie tak
    if (!isset($layout_css_map[$layout])) {
        $layout = 'layout1';
    }

    // Wybierz odpowiedni plik CSS
    if (is_front_page()) {
        $css_file = get_theme_file_path($layout_css_map[$layout]['front']);
        $css_uri  = get_theme_file_uri($layout_css_map[$layout]['front']);
    } elseif (is_home()) {
        $css_file = get_theme_file_path($layout_css_map[$layout]['blog']);
        $css_uri  = get_theme_file_uri($layout_css_map[$layout]['blog']);
    } else {
        $css_file = get_theme_file_path($layout_css_map[$layout]['other']);
        $css_uri  = get_theme_file_uri($layout_css_map[$layout]['other']);
    }

    // Sprawdź czy plik istnieje i załaduj
    if (file_exists($css_file)) {
        wp_enqueue_style(
            'adm--header-layout',
            $css_uri,
            array(),
            '1.0.3',
            'all'
        );
    }

}, 22);




