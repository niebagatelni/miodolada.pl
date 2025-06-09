<?php
// Rejestracja metadanych dla postów i stron w WordPressie
function adm_register_post_metadata() {
    // Pobranie wszystkich typów postów
    $post_types = get_post_types([], 'names');

    foreach ($post_types as $post_type) {
        register_post_meta($post_type, 'adm_post_keywords', [
            'show_in_rest' => true,
            'single'       => true,
            'type'         => 'string',
            'sanitize_callback' => 'sanitize_text_field',
        ]);

        register_post_meta($post_type, 'adm_post_description', [
            'show_in_rest' => true,
            'single'       => true,
            'type'         => 'string',
            'sanitize_callback' => 'sanitize_textarea_field',
        ]);

        register_post_meta($post_type, 'adm_post_title', [
            'show_in_rest' => true,
            'single'       => true,
            'type'         => 'string',
            'sanitize_callback' => 'sanitize_text_field',
        ]);
    }
}
add_action('init', 'adm_register_post_metadata');

// Dodanie skryptu JS do Gutenberga
function adm_enqueue_post_metadata_script() {
    wp_enqueue_script(
        'adm-post-metadata',
        get_stylesheet_directory_uri() . '/adm-inc/js/post-metadata.js',
        ['wp-plugins', 'wp-edit-post', 'wp-components', 'wp-data', 'wp-element'],
        filemtime(get_stylesheet_directory() . '/adm-inc/js/post-metadata.js'),
        true
    );
}
add_action('enqueue_block_editor_assets', 'adm_enqueue_post_metadata_script');
