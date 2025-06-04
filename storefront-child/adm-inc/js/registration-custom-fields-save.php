<?php

// Dodanie billing_vat jako pełnoprawnego pola rozliczeniowego WooCommerce
function dodaj_pole_billing_vat_do_danych_rozliczeniowych( $fields ) {
    $fields['billing_vat'] = array(
        'label'       => __('NIP', 'storefront-child'),
        'required'    => true,
        'class'       => array('form-row-wide'),
        'clear'       => true,
        'priority'    => 120,
    );
    return $fields;
}
add_filter('woocommerce_billing_fields', 'dodaj_pole_billing_vat_do_danych_rozliczeniowych');


// Zapis danych klienta po rejestracji
function zapisz_dane_rejestracji_i_przypisz_role($customer_id) {
    // Przypisywanie roli "zainteresowany_oferta"
    $user = new WP_User($customer_id);
    $user->set_role('zainteresowany_oferta');
    $user->remove_role('customer');

    // Pola billingowe WooCommerce
    $woocommerce_fields = ['billing_company', 'billing_address_1', 'billing_address_2', 'billing_phone', 'billing_vat'];

    foreach ($woocommerce_fields as $field) {
        if (isset($_POST[$field])) {
            // Sprawdzamy, czy adresy są ustawione
            if ($field === 'billing_address_1' || $field === 'billing_address_2') {
                update_user_meta($customer_id, $field, sanitize_textarea_field($_POST[$field]));
            } else {
                update_user_meta($customer_id, $field, sanitize_text_field($_POST[$field]));
            }
        }
    }

    // Ustawienie billing_vat jako meta danych klienta WooCommerce
    $customer = new WC_Customer($customer_id);
    if (isset($_POST['billing_vat'])) {
        $customer->update_meta_data('billing_vat', sanitize_text_field($_POST['billing_vat']));
    }
    $customer->save();

    // Dodatkowe pole niestandardowe - rodzaj działalności
    if (isset($_POST['rodzaj_dzialalnosci'])) {
        update_user_meta(
            $customer_id,
            'rodzaj_dzialalnosci',
            sanitize_text_field($_POST['rodzaj_dzialalnosci'])
        );
    }


/*
    // Wysłanie powiadomienia do administratora o nowej rejestracji
    $admin_email = get_option('admin_email');
    $subject = 'Nowa rejestracja - Zainteresowany ofertą';
    $user_data = get_userdata($customer_id);
    
    $message = sprintf(
        'Nowy użytkownik zarejestrował się jako "Zainteresowany ofertą":<br><br>
        Email: %s<br>
        Nazwa firmy: %s<br>
        Rodzaj działalności: %s<br>
        NIP: %s<br>
        Adres: %s %s<br>
        Telefon: %s<br><br>
        Przejdź do panelu administratora, aby zobaczyć pełne informacje.',
        $user_data->user_email,
        get_user_meta($customer_id, 'billing_company', true),
        get_user_meta($customer_id, 'rodzaj_dzialalnosci', true),
        get_user_meta($customer_id, 'billing_vat', true),
        get_user_meta($customer_id, 'billing_address_1', true),
        get_user_meta($customer_id, 'billing_address_2', true),
        get_user_meta($customer_id, 'billing_phone', true)
    );

    $headers = array('Content-Type: text/html; charset=UTF-8');

    // Wysłanie maila, jeśli nie uda się, to zapisz błąd
    if (!wp_mail($admin_email, $subject, $message, $headers)) {
        error_log('Błąd przy wysyłaniu maila powiadamiającego administratora o rejestracji.');
        if( function_exists('adm_log3') ) adm_log3('Błąd przy wysyłaniu maila powiadamiającego administratora o rejestracji do `Zainteresowany ofertą`.');
    }
*/


}
add_action('woocommerce_created_customer', 'zapisz_dane_rejestracji_i_przypisz_role');
// Dodanie billing_vat jako pełnoprawnego pola rozliczeniowego WooCommerce
function dodaj_pole_billing_vat_do_danych_rozliczeniowych( $fields ) {
    $fields['billing_vat'] = array(
        'label'       => __('NIP', 'storefront-child'),
        'required'    => true,
        'class'       => array('form-row-wide'),
        'clear'       => true,
        'priority'    => 120,
    );
    return $fields;
}
add_filter('woocommerce_billing_fields', 'dodaj_pole_billing_vat_do_danych_rozliczeniowych');


// Zapis danych klienta po rejestracji
function zapisz_dane_rejestracji_i_przypisz_role($customer_id) {
    // Przypisywanie roli "zainteresowany_oferta"
    $user = new WP_User($customer_id);
    $user->set_role('zainteresowany_oferta');
    $user->remove_role('customer');

    // Pola billingowe WooCommerce
    $woocommerce_fields = ['billing_company', 'billing_address_1', 'billing_address_2', 'billing_phone', 'billing_vat'];

    foreach ($woocommerce_fields as $field) {
        if (isset($_POST[$field])) {
            // Sprawdzamy, czy adresy są ustawione
            if ($field === 'billing_address_1' || $field === 'billing_address_2') {
                update_user_meta($customer_id, $field, sanitize_textarea_field($_POST[$field]));
            } else {
                update_user_meta($customer_id, $field, sanitize_text_field($_POST[$field]));
            }
        }
    }

    // Ustawienie billing_vat jako meta danych klienta WooCommerce
    $customer = new WC_Customer($customer_id);
    if (isset($_POST['billing_vat'])) {
        $customer->update_meta_data('billing_vat', sanitize_text_field($_POST['billing_vat']));
    }
    $customer->save();

    // Dodatkowe pole niestandardowe - rodzaj działalności
    if (isset($_POST['rodzaj_dzialalnosci'])) {
        update_user_meta(
            $customer_id,
            'rodzaj_dzialalnosci',
            sanitize_text_field($_POST['rodzaj_dzialalnosci'])
        );
    }


/*
    // Wysłanie powiadomienia do administratora o nowej rejestracji
    $admin_email = get_option('admin_email');
    $subject = 'Nowa rejestracja - Zainteresowany ofertą';
    $user_data = get_userdata($customer_id);
    
    $message = sprintf(
        'Nowy użytkownik zarejestrował się jako "Zainteresowany ofertą":<br><br>
        Email: %s<br>
        Nazwa firmy: %s<br>
        Rodzaj działalności: %s<br>
        NIP: %s<br>
        Adres: %s %s<br>
        Telefon: %s<br><br>
        Przejdź do panelu administratora, aby zobaczyć pełne informacje.',
        $user_data->user_email,
        get_user_meta($customer_id, 'billing_company', true),
        get_user_meta($customer_id, 'rodzaj_dzialalnosci', true),
        get_user_meta($customer_id, 'billing_vat', true),
        get_user_meta($customer_id, 'billing_address_1', true),
        get_user_meta($customer_id, 'billing_address_2', true),
        get_user_meta($customer_id, 'billing_phone', true)
    );

    $headers = array('Content-Type: text/html; charset=UTF-8');

    // Wysłanie maila, jeśli nie uda się, to zapisz błąd
    if (!wp_mail($admin_email, $subject, $message, $headers)) {
        error_log('Błąd przy wysyłaniu maila powiadamiającego administratora o rejestracji.');
        if( function_exists('adm_log3') ) adm_log3('Błąd przy wysyłaniu maila powiadamiającego administratora o rejestracji do `Zainteresowany ofertą`.');
    }
*/


}
add_action('woocommerce_created_customer', 'zapisz_dane_rejestracji_i_przypisz_role');
