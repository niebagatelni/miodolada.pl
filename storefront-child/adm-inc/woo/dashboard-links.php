<?php


// Dodaj link "Zmień hasło" w sekcji Moje konto
add_filter('woocommerce_account_menu_items', 'dodaj_link_zmien_haslo', 40);
function dodaj_link_zmien_haslo($items) {
    // Wstawiamy link po Pulpit (dashboard)
    $nowe = array();

    foreach ($items as $key => $value) {
        $nowe[$key] = $value;

        if ($key === 'dashboard') {
            $nowe['zmien-haslo'] = __('Zmień hasło', 'woocommerce');
        }
    }

    return $nowe;
}

add_filter('woocommerce_get_endpoint_url', 'ustaw_url_zmien_haslo', 10, 2);
function ustaw_url_zmien_haslo($url, $endpoint) {
    if ($endpoint === 'zmien-haslo') {
        return wc_get_account_endpoint_url('edit-account');
    }
    return $url;
}



// Dodaj link do resetu hasła pod polami zmiany hasła
add_action('woocommerce_edit_account_form', 'dodaj_link_reset_hasla_pod_polami', 0);
function dodaj_link_reset_hasla_pod_polami() {
    if (!is_user_logged_in()) {
        return;
    }
    $reset_url = wp_lostpassword_url();
    echo '<p id="adm--lost-pasword-link">';
    echo 'Nie pamiętasz aktualnego hasła?<br><a href="' . esc_url($reset_url) . '">Zresetuj je tutaj</a>.';
    echo '</p>';
}


