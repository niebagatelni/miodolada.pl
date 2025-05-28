<?php

// Modyfikacja formularza rejestracji WooCommerce
function dodaj_pola_rejestracji_woocommerce() {


/*
woocommerce_form_field( 'password', [
    'type'        => 'password',
    'label'       => __( 'Hasło', 'storefront-child' ),
    'required'    => true,
    'class'       => ['woocommerce-form-row--wide', 'form-row-wide'],
    'label_class' => ['woocommerce-form__label'],
], isset( $_POST['password'] ) ? wc_clean( $_POST['password'] ) : '' );
*/


    // Potwierdzenie hasła
    woocommerce_form_field( 'confirm_password', [
        'type'            => 'password',
        'label'           => __( 'Potwierdź hasło', 'storefront-child' ),
        'required'        => true,
        'class'           => ['woocommerce-form-row--wide','form-row-wide'],
        'label_class'     => ['woocommerce-form__label'],
    ], isset( $_POST['confirm_password'] ) ? wc_clean( $_POST['confirm_password'] ) : '' );

    // Nazwa firmy
    woocommerce_form_field( 'billing_company', [
        'type'            => 'text',
        'label'           => __( 'Nazwa firmy', 'storefront-child' ),
        'required'        => true,
        'class'           => ['woocommerce-form-row--wide','form-row-wide'],
        'label_class'     => ['woocommerce-form__label'],
    ], isset( $_POST['billing_company'] ) ? wc_clean( $_POST['billing_company'] ) : '' );

    // Rodzaj działalności
    woocommerce_form_field( 'rodzaj_dzialalnosci', [
        'type'            => 'text',
        'label'           => __( 'Rodzaj działalności', 'storefront-child' ),
        'required'        => true,
        'class'           => ['woocommerce-form-row--wide','form-row-wide'],
        'label_class'     => ['woocommerce-form__label'],
    ], isset( $_POST['rodzaj_dzialalnosci'] ) ? wc_clean( $_POST['rodzaj_dzialalnosci'] ) : '' );


    // NIP
    woocommerce_form_field( 'billing_vat', [
        'type'            => 'text',
        'label'           => __( 'NIP', 'storefront-child' ),
        'required'        => true,
        'class'           => ['woocommerce-form-row--wide','form-row-wide'],
        'label_class'     => ['woocommerce-form__label'],
    ], isset( $_POST['billing_vat'] ) ? wc_clean( $_POST['billing_vat'] ) : '' );


    // Adres firmy
    woocommerce_form_field( 'billing_address_1', [
        'type'            => 'text',
        'label'           => __( 'Adres firmy', 'storefront-child' ),
        'required'        => true,
        'class'           => ['woocommerce-form-row--wide','form-row-wide'],
        'label_class'     => ['woocommerce-form__label'],
    ], isset( $_POST['billing_address_1'] ) ? wc_clean( $_POST['billing_address_1'] ) : '' );

    // Adres 2
    woocommerce_form_field( 'billing_address_2', [
        'type'            => 'text',
        'label'           => __( 'Adres 2', 'storefront-child' ),
        'required'        => false,
        'class'           => ['woocommerce-form-row--wide','form-row-wide'],
        'label_class'     => ['woocommerce-form__label'],
    ], isset( $_POST['billing_address_2'] ) ? wc_clean( $_POST['billing_address_2'] ) : '' );

    // Numer telefonu
    woocommerce_form_field( 'billing_phone', [
        'type'            => 'tel',
        'label'           => __( 'Numer telefonu', 'storefront-child' ),
        'required'        => true,
        'class'           => ['woocommerce-form-row--wide','form-row-wide'],
        'label_class'     => ['woocommerce-form__label']
    ], isset( $_POST['billing_phone'] ) ? wc_clean( $_POST['billing_phone'] ) : '' );
}

add_action('woocommerce_register_form', 'dodaj_pola_rejestracji_woocommerce');




function dodaj_inline_js_do_rejestracji() {
        ?>
        <script>
        document.addEventListener("DOMContentLoaded", function () {

        var pole = "#billing_phone";
        if( document.querySelector(pole) ){
            document.querySelector(pole).value="654654654";
        }

        var pole = "#billing_vat";
        if( document.querySelector(pole) ){
            document.querySelector(pole).value="9462600874";
        }

        var pole = "#billing_address_1";
        if( document.querySelector(pole) ){
            document.querySelector(pole).value="adres linia 1";
        }

        var pole = "#rodzaj_dzialalnosci";
        if( document.querySelector(pole) ){
            document.querySelector(pole).value = "sklep";
        }

        var pole = "#billing_company";
        if( document.querySelector(pole) ){
            document.querySelector(pole).value = "firma billing company";
        }

        var pole = "#reg_password";
        if( document.querySelector(pole) ){
            document.querySelector(pole).value = "qweqwe";
        }
        var pole = "#confirm_password";
        if( document.querySelector(pole) ){
            document.querySelector(pole).value = "qweqwe";
        }





        });
        </script>
        <?php
}
add_action('wp_footer', 'dodaj_inline_js_do_rejestracji');



// Walidacja danych w formularzu rejestracji
function walidacja_pol_formularza_rejestracji($errors, $username, $email) {
 

function waliduj_nip($nip) {
    $nip = preg_replace('/[^0-9]/', '', $nip);
    
    if (strlen($nip) != 10) {
        return false;
    }
    
    // Sprawdzenie NIPu wg. polskich standardów, wg. wagi i cyfr kontrolnych
    $wagi = [6, 5, 7, 2, 3, 4, 5, 6, 7];
    
    $suma = 0;
    for ($i = 0; $i < 9; $i++) {
        $suma += $nip[$i] * $wagi[$i];
    }
    
    $suma %= 11;
    
    if ($suma == $nip[9]) {
        return true;
    }
    
    return false;
}


if (empty($_POST['password'])) {
    $errors->add('password_error', __('Hasło jest wymagane.', 'storefront-child'));
}

if ($_POST['password'] !== $_POST['confirm_password']) {
    $errors->add('password_mismatch', __('Hasła nie są takie same.', 'storefront-child'));
}

   // Sprawdzanie, czy wymagane pola są wypełnione
    if (empty($_POST['billing_company'])) {
        $errors->add('billing_company_error', __('Nazwa firmy jest wymagana.', 'storefront-child'));
    }

    if (empty($_POST['rodzaj_dzialalnosci'])) {
        $errors->add('rodzaj_dzialalnosci_error', __('Rodzaj działalności jest wymagany.', 'storefront-child'));
    }

    if (empty($_POST['billing_address_1'])) {
        $errors->add('billing_address_1_error', __('Adres firmy jest wymagany.', 'storefront-child'));
    }

    // Walidacja numeru NIP
    if (empty($_POST['billing_vat'])) {
        $errors->add('billing_vat_error', __('Numer NIP jest wymagany.', 'storefront-child'));
    } elseif (!waliduj_nip($_POST['billing_vat'])) {
        $errors->add('billing_vat_error', __('Podany numer NIP jest nieprawidłowy.', 'storefront-child'));
    }

    // Walidacja numeru telefonu
    if (empty($_POST['billing_phone'])) {
        $errors->add('billing_phone_error', __('Numer telefonu jest wymagany.', 'storefront-child'));
    }


    return $errors;
}

add_filter('woocommerce_registration_errors', 'walidacja_pol_formularza_rejestracji', 10, 3);



//add_filter('woocommerce_billing_fields', 'dodaj_pole_billing_vat_do_billing_fields');


function dodaj_pole_billing_vat_do_billing_fields($fields) {
    $fields['billing_vat'] = array(
        'label'       => __('NIP', 'storefront-child'),
        'placeholder' => __('Wpisz NIP', 'storefront-child'),
        'required'    => true,
        'class'       => array('form-row-wide'),
        'clear'       => true,
        'priority'    => 120
    );
    return $fields;
}





// Zapisanie dodatkowych pól jako metadane użytkownika
function zapisanie_dodatkowych_pól_użytkownika($user_id) {

    if (isset($_POST['rodzaj_dzialalnosci'])) {
        update_user_meta($user_id, 'rodzaj_dzialalnosci', sanitize_text_field($_POST['rodzaj_dzialalnosci']));
    }

    if (isset($_POST['billing_vat'])) {
        update_user_meta($user_id, 'billing_vat', sanitize_text_field($_POST['billing_vat']));
    }


    if (!empty($_POST['password'])) {
        wp_set_password(sanitize_text_field($_POST['password']), $user_id);
    }

}

add_action('woocommerce_created_customer', 'zapisanie_dodatkowych_pól_użytkownika');




