<?php
// Handler aktywacji konta klienta po kliknięciu w link z e-maila administratora.
// Po kliknięciu linku admina, zmienia rolę użytkownika z 'zainteresowany_oferta' na 'klient_hurtowy'  i usuwa token.

$fname_log = '[' . basename(__FILE__, '.php') . '] ';


// Handler zmiany roli użytkownika po kliknięciu w link z tokenem
add_action('init', function() {
    if (!isset($_GET['change_role']) || !isset($_GET['token'])) {
        return;
    }

    $token = sanitize_text_field($_GET['token']);
    if (empty($token)) {
        if (function_exists('adm_log3')) adm_log3( 'Brak tokena lub token jest pusty');
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
        if (function_exists('adm_log3')) adm_log3('Nieprawidłowy lub już wykorzystany link (...)');
        wp_die('Nieprawidłowy lub już wykorzystany link.');
    }
    $user_id = $users[0];

    // Sprawdź datę wygaśnięcia tokena
    $expires = get_user_meta($user_id, '_role_change_token_expires', true);
    if (!$expires || time() > intval($expires)) {
        if (function_exists('adm_log3')) adm_log3( 'Link wygasł dla user_id: ' . $user_id);
        // Usuwamy token, nawet jeśli wygasł
        delete_user_meta($user_id, '_role_change_token');
        delete_user_meta($user_id, '_role_change_token_expires');
        wp_die('Link wygasł. Skontaktuj się z administratorem.');
    }

    // Pobierz użytkownika
    $user = get_userdata($user_id);
    if (!$user) {
        if (function_exists('adm_log3')) adm_log3( 'Nie znaleziono użytkownika o ID: ' . $user_id);
        wp_die('Nie znaleziono użytkownika.');
    }

    // Sprawdź, czy użytkownik ma rolę "zainteresowany_oferta"
    if (!in_array('zainteresowany_oferta', (array)$user->roles, true)) {
        // Usuwamy token, bo nie powinien już być aktywny
        delete_user_meta($user_id, '_role_change_token');
        delete_user_meta($user_id, '_role_change_token_expires');
        wp_die('Ten użytkownik nie wymaga aktywacji lub już został zaakceptowany.');
    }


    // Zmień rolę na "klient_hurtowy" i usuń "zainteresowany_oferta"
    $user->set_role('klient_hurtowy');
    $user->remove_role('zainteresowany_oferta');

    
    // Sprawdź, czy rola została zmieniona
    $user_check = get_userdata($user_id);
    if (!$user_check || !in_array('klient_hurtowy', (array)$user_check->roles, true)) {
        if (function_exists('adm_log3')) adm_log3( 'BŁĄD: Rola NIE została zmieniona na klient_hurtowy dla user_id: ' . $user_id);
        wp_die('Błąd: rola NIE została zmieniona. Skontaktuj się z administratorem.');
    }
    if (function_exists('adm_log3')) adm_log3( 'Rola poprawnie zmieniona na klient_hurtowy dla user_id: ' . $user_id);

    // Usuń token, aby link był jednorazowy
    delete_user_meta($user_id, '_role_change_token');
    delete_user_meta($user_id, '_role_change_token_expires');


    // SPRAWDZENIE WSZYSTKICH ZMIAN NA KONIEC
    $user_check = get_userdata($user_id);
    $rola_ok = $user_check && in_array('klient_hurtowy', (array)$user_check->roles, true);
    $zainteresowany_oferta_ok = $user_check && !in_array('zainteresowany_oferta', (array)$user_check->roles, true);
    $token_ok = empty(get_user_meta($user_id, '_role_change_token', true));
    $expires_ok = empty(get_user_meta($user_id, '_role_change_token_expires', true));


    if (!$rola_ok || !$zainteresowany_oferta_ok || !$token_ok || !$expires_ok) {
        if (function_exists('adm_log3')) {
            $log_data = [
                'rola_ok' => $rola_ok ? 'OK' : 'BRAK',
                'zainteresowany_oferta' => $zainteresowany_oferta_ok ? 'USUNIĘTA' : 'JEST',
                'token' => $token_ok ? 'USUNIĘTY' : 'ISTNIEJE',
                'expires' => $expires_ok ? 'USUNIĘTY' : 'ISTNIEJE',
                'user_id' => $user_id,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'time' => date('Y-m-d H:i:s'),
                'referrer' => $_SERVER['HTTP_REFERER'] ?? '',
                'host' => $_SERVER['HTTP_HOST'] ?? '',
            ];
            adm_log3( 'BŁĄD: Po aktywacji: ' . print_r($log_data, true));
        }

        wp_die('Błąd: nie wszystkie zmiany zostały wykonane. Skontaktuj się z administratorem.');
    }

    
    // Komunikat końcowy dla admina po kliknięciu linku
wp_die('Konto klienta zostało pomyślnie aktywowane. Może już dokonać zakupu');

});

