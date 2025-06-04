<?php
// Handler zmiany roli użytkownika po kliknięciu w link z tokenem
add_action('init', function() {
    if (!isset($_GET['change_role']) || !isset($_GET['token'])) {
        return;
    }

    $token = sanitize_text_field($_GET['token']);
    if (empty($token)) {
        wp_die('Brak tokena lub token jest nieprawidłowy.');
    }

    // Szukamy użytkownika po user_meta _role_change_token
    $args = array(
        'meta_key' => '_role_change_token',
        'meta_value' => $token,
        'number' => 1,
        'fields' => 'ID',
    );
    $users = get_users($args);
    if (empty($users)) {
        wp_die('Nieprawidłowy lub już wykorzystany link.');
    }
    $user_id = $users[0];

    // Sprawdź datę wygaśnięcia tokena
    $expires = get_user_meta($user_id, '_role_change_token_expires', true);
    if (!$expires || time() > intval($expires)) {
        // Usuwamy token, nawet jeśli wygasł
        delete_user_meta($user_id, '_role_change_token');
        delete_user_meta($user_id, '_role_change_token_expires');
        wp_die('Link wygasł. Skontaktuj się z administratorem.');
    }

    // Pobierz użytkownika
    $user = get_userdata($user_id);
    if (!$user) {
        wp_die('Nie znaleziono użytkownika.');
    }

    // Sprawdź, czy użytkownik ma rolę "zainteresowany_oferta"
    if (!in_array('zainteresowany_oferta', (array)$user->roles, true)) {
        // Usuwamy token, bo nie powinien już być aktywny
        delete_user_meta($user_id, '_role_change_token');
        delete_user_meta($user_id, '_role_change_token_expires');
        wp_die('Ten użytkownik nie wymaga już aktywacji lub już został zaakceptowany.');
    }

    // Zmień rolę na customer
    $user->set_role('customer');

    // Usuń token, aby link był jednorazowy
    delete_user_meta($user_id, '_role_change_token');
    delete_user_meta($user_id, '_role_change_token_expires');

    // Komunikat końcowy
    wp_die('Konto zostało pomyślnie aktywowane. Użytkownik ma teraz dostęp do cen hurtowych.');
});
