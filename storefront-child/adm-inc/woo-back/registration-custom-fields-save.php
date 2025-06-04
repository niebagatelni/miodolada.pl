<?php

function zapisz_dane_rejestracji_i_przypisz_role($customer_id) {
    // Przypisywanie roli "zainteresowany_oferta"
    $user = new WP_User($customer_id);
    $user->set_role('zainteresowany_oferta');
    $user->remove_role('customer');

    // Standardowe pola WooCommerce
	$woocommerce_fields = ['billing_vat'];
    
    foreach ($woocommerce_fields as $field) {
        if (isset($_POST[$field])) {
            if ($field === 'customer_note') {
                update_user_meta($customer_id, $field, sanitize_textarea_field($_POST[$field]));
            } else {
                update_user_meta($customer_id, $field, sanitize_text_field($_POST[$field]));
            }
        }
    }
    
    // Dodatkowe pole niestandardowe - rodzaj działalności
    if (isset($_POST['rodzaj_dzialalnosci'])) {
        update_user_meta(
            $customer_id,
            'rodzaj_dzialalnosci',
            sanitize_text_field($_POST['rodzaj_dzialalnosci'])
        );
    }
    
    // Wysłanie powiadomienia do administratora o nowej rejestracji
    $admin_email = get_option('admin_email');
    $subject = 'Nowa rejestracja - Zainteresowany ofertą';
    $user_data = get_userdata($customer_id);
    
    $message = sprintf(
        'Nowy użytkownik zarejestrował się jako "Zainteresowany ofertą":<br><br>
        Email: %s<br>
        Nazwa firmy: %s<br>
        Rodzaj działalności: %s<br>
        NIP: %s<br><br>
        Przejdź do panelu administratora, aby zobaczyć pełne informacje.',
        $user_data->user_email,
        get_user_meta($customer_id, 'billing_company', true),
        get_user_meta($customer_id, 'rodzaj_dzialalnosci', true),
        get_user_meta($customer_id, 'billing_vat', true)
    );
    
    $headers = array('Content-Type: text/html; charset=UTF-8');
    
    wp_mail($admin_email, $subject, $message, $headers);
}

add_action('woocommerce_created_customer', 'zapisz_dane_rejestracji_i_przypisz_role');