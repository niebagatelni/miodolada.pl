<?php


// Rejestrujemy akcję czyszczenia cache
add_action('admin_post_adm_clear_object_cache', function () {
    // Sprawdzenie uprawnień
    if ( ! current_user_can('manage_options') ) {
        wp_die('Brak uprawnień');
    }

    // Sprawdzenie nonce'a
    check_admin_referer('adm_clear_object_cache_action');

    // Czyszczenie cache
    wp_cache_flush();

    // Przekierowanie z komunikatem
    wp_redirect(
        add_query_arg('adm_cache_flushed', '1', admin_url())
    );
    exit;
});

// Wyświetlamy komunikat po czyszczeniu cache
add_action('admin_notices', function () {
    if (isset($_GET['adm_cache_flushed']) && $_GET['adm_cache_flushed'] === '1') {
        echo '<div class="notice notice-success is-dismissible"><p>Cache został wyczyszczony.</p></div>';
    }
});

// Dodajemy przycisk w panelu admina (np. na kokpicie)
add_action('admin_footer', function () {
    if ( ! current_user_can('manage_options') ) return;

    $url = admin_url('admin-post.php?action=adm_clear_object_cache&_wpnonce=' . wp_create_nonce('adm_clear_object_cache_action'));
    echo '<div class="wrap" style="padding: 1em 0;">
        <a href="' . esc_url($url) . '" class="button button-primary">🧹 Wyczyść cache obiektowy</a>
    </div>';
});



