<?php

//
// Modyfikacja formularza rejestracji WooCommerce
//


function dodaj_pola_rejestracji_woocommerce() {
    ?>
    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
        <label for="rodzaj_dzialalnosci"><?php esc_html_e('Rodzaj działalności', 'storefront-child'); ?> <span class="required">*</span></label>
        <input type="text" name="rodzaj_dzialalnosci" id="rodzaj_dzialalnosci" value="<?php echo isset($_POST['rodzaj_dzialalnosci']) ? esc_attr($_POST['rodzaj_dzialalnosci']) : ''; ?>" required />
    </p>

    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
        <label for="billing_vat"><?php esc_html_e('NIP', 'storefront-child'); ?> <span class="required">*</span></label>
        <input type="text" name="billing_vat" id="billing_vat" value="<?php echo isset($_POST['billing_vat']) ? esc_attr($_POST['billing_vat']) : ''; ?>" required />
    </p>
    <?php
}

add_action('woocommerce_register_form', 'dodaj_pola_rejestracji_woocommerce');




 //
 // Walidacja danych w formularzu rejestracji
 //
 // @param WP_Error $errors Obiekt WP_Error zawierający dotychczasowe błędy
 // @param string $username Nazwa użytkownika
 // @param string $email Adres email
 // @return WP_Error Obiekt WP_Error z dodanymi błędami walidacji
 //


function walidacja_pol_formularza_rejestracji($errors, $username, $email) {
    // Sprawdzanie czy wymagane pola są wypełnione
    if (empty($_POST['billing_company'])) {
        $errors->add('billing_company_error', __('Nazwa firmy jest wymagana.', 'storefront-child'));
    }

    if (empty($_POST['rodzaj_dzialalnosci'])) {
        $errors->add('rodzaj_dzialalnosci_error', __('Rodzaj działalności jest wymagany.', 'storefront-child'));
    }

    if (empty($_POST['billing_address_1'])) {
        $errors->add('billing_address_1_error', __('Adres firmy jest wymagany.', 'storefront-child'));
    }
    
    if (empty($_POST['billing_postcode'])) {
        $errors->add('billing_postcode_error', __('Kod pocztowy jest wymagany.', 'storefront-child'));
    }
    
    if (empty($_POST['billing_city'])) {
        $errors->add('billing_city_error', __('Miasto jest wymagane.', 'storefront-child'));
    }

    // Walidacja numeru NIP
    if (empty($_POST['billing_vat'])) {
        $errors->add('billing_vat_error', __('Numer NIP jest wymagany.', 'storefront-child'));
    } elseif (!waliduj_nip($_POST['billing_vat'])) {
        $errors->add('billing_vat_error', __('Podany numer NIP jest nieprawidłowy. Upewnij się, że wpisałeś 10 cyfr i poprawny numer.', 'storefront-child'));
    }

    return $errors;
}

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

add_filter('woocommerce_registration_errors', 'walidacja_pol_formularza_rejestracji', 10, 3);

