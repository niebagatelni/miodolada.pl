<?php

// Walidacja NIP - przenieś tę funkcję globalnie
if (!function_exists('waliduj_nip')){
function waliduj_nip($nip) {
    $nip = preg_replace('/[^0-9]/', '', $nip);
    if (strlen($nip) != 10) {
        return false;
    }
    $wagi = [6, 5, 7, 2, 3, 4, 5, 6, 7];
    $suma = 0;
    for ($i = 0; $i < 9; $i++) {
        $suma += $nip[$i] * $wagi[$i];
    }
    $suma %= 11;
    return $suma == $nip[9];
}}


// Dodanie pola NIP do checkoutu blokowego
add_action('woocommerce_init', function () {
    if (!function_exists('woocommerce_register_additional_checkout_field')) return;

    woocommerce_register_additional_checkout_field([
        'id'       => 'custom/billing_tax_no',
        'label'    => 'NIP',
        'location' => 'address',
        'type'     => 'text',
        'required' => true,
        'attributes' => [
            'autocomplete' => 'billing-tax-id',
        ],
    ]);
});


// Zapis wartości pola do zamówienia i użytkownika
add_action('woocommerce_set_additional_field_value', function ($key, $value, $group, $wc_object) {
    if ('custom/billing_tax_no' !== $key) {
        return;
    }

    $sanitized = sanitize_text_field($value);

    if (is_a($wc_object, WC_Order::class)) {
        $wc_object->update_meta_data('_billing_tax_no', $sanitized);
        $wc_object->update_meta_data('_billing_vat', $sanitized);
    }

    if (is_a($wc_object, WP_User::class)) {
        update_user_meta($wc_object->ID, 'billing_tax_no', $sanitized);
        update_user_meta($wc_object->ID, 'billing_vat', $sanitized);
    }
}, 10, 4);



// Odczyt wartości pola dla zalogowanych użytkowników
add_filter('woocommerce_get_additional_field_value', function ($value, $key, $group, $wc_object) {
    if ('custom/billing_tax_no' === $key && is_a($wc_object, WP_User::class)) {
        return get_user_meta($wc_object->ID, 'billing_tax_no', true);
    }
    return $value;
}, 10, 4);



// Wyświetlenia NIP w Moje konto w adresie rozliczeniowym
add_filter('woocommerce_my_account_my_address_formatted_address', function ($address, $customer_id, $address_type) {
    if ($address_type === 'billing') {
        $nip = get_user_meta($customer_id, 'billing_tax_no', true);
        if (!empty($nip)) {
            $address['nip'] = 'NIP z checkout-changes: ' . $nip;
        }
    }
    return $address;
}, 10, 3);










/*  KLASYCZNY CHECKOUT  [woocommerce_checkout]  */

// Dodanie pola NIP (billing_tax_no) do danych rozliczeniowych WooCommerce (do billing fields)
function dodaj_pole_billing_tax_no_do_danych_rozliczeniowych( $fields ) {
    $fields['billing_tax_no'] = array(
        'label'       => __('NIPpp', 'storefront-child'),
        'placeholder' => _x('NIP', 'placeholder', 'storefront-child'),
        'required'    => true,
        'class'       => array('form-row-wide'),
        'clear'       => true,
        'priority'    => 120,
        'type'        => 'text',
    );
    return $fields;
}
add_action('init', function() {
    add_filter('woocommerce_billing_fields', 'dodaj_pole_billing_tax_no_do_danych_rozliczeniowych');
});


// Walidacja NIP przy zamówieniu (checkout)
if (function_exists('waliduj_nip')){
    add_action('woocommerce_checkout_process', function() {
        if (empty($_POST['billing_tax_no'])) {
            wc_add_notice(__('Numer NIP jest wymagany.', 'storefront-child'), 'error');
        } elseif (!waliduj_nip($_POST['billing_tax_no'])) {
            wc_add_notice(__('Podany numer NIP jest nieprawidłowy.', 'storefront-child'), 'error');
        }
    });
}

// Usunięcie pola NIP z pól wysyłkowych (z shipping fields)
add_filter('woocommerce_shipping_fields', function($fields) {
    if (isset($fields['billing_tax_no'])) {
        unset($fields['billing_tax_no']);
    }
    return $fields;
});
