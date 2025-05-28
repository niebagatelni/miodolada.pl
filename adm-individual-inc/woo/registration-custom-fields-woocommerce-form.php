<?php


// Wyłącz domyślny e-mail WooCommerce o utworzeniu konta TYLKO podczas rejestracji przez ten shortcode
add_filter('woocommerce_email_enabled_customer_new_account', function($enabled) {
    if (isset($_POST['custom_registration_form_submitted'])) {
        return false;
    }
    return $enabled;
});

// Wspólna definicja pól rejestracji
global $adm_registration_fields;
$adm_registration_fields = [
    'first_name' => [
        'type' => 'text',
        'label' => __( 'Imię', 'storefront-child' ),
        'required' => true,
    ],
    'last_name' => [
        'type' => 'text',
        'label' => __( 'Nazwisko', 'storefront-child' ),
        'required' => false,
    ],
    'billing_vat' => [
        'type' => 'text',
        'label' => __( 'NIP', 'storefront-child' ),
        'required' => true,
    ],
    'billing_company' => [
        'type' => 'text',
        'label' => __( 'Nazwa firmy', 'storefront-child' ),
        'required' => true,
    ],
    'billing_address_1' => [
        'type' => 'text',
        'label' => __( 'Adres firmy', 'storefront-child' ),
        'required' => true,
    ],
    'billing_address_2' => [
        'type' => 'text',
        'label' => __( 'Adres c.d.', 'storefront-child' ),
        'required' => false,
    ],
    'billing_phone' => [
        'type' => 'tel',
        'label' => __( 'Numer telefonu', 'storefront-child' ),
        'required' => false,
    ],
    'company_additional_info' => [
        'type' => 'textarea',
        'label' => __( 'Dodatkowe informacje o firmie', 'storefront-child' ),
        'required' => false,
    ],
    'rodzaj_dzialalnosci' => [
        'type' => 'select',
        'label' => __( 'Rodzaj działalności', 'storefront-child' ),
        'required' => true,
        'options' => [
            '' => __( 'Wybierz rodzaj działalności', 'storefront-child' ),
            'sklep-internetowy' => __( 'Sklep internetowy', 'storefront-child' ),
            'sklep-spozywczy' => __( 'Sklep spożywczy', 'storefront-child' ),
            'zdrowa-zywnosc' => __( 'Sklep ze zdrową żywnością', 'storefront-child' ),
            'kawa-herbata' => __( 'Sklep z kawą/herbatą', 'storefront-child' ),
            'gastronomia' => __( 'Lokal gastronomiczny', 'storefront-child' ),
            'hurtownia' => __( 'Hurtownia', 'storefront-child' ),
            'inny' => __( 'Inny', 'storefront-child' ),
        ],
    ],
];

// Formularz WooCommerce
function dodaj_pola_rejestracji_woocommerce() {
    global $adm_registration_fields;
    // Email (pole domyślne WooCommerce, nie trzeba dodawać)
    woocommerce_form_field( 'confirm_password', [
        'type'        => 'password',
        'label'       => __( 'Potwierdź hasło', 'storefront-child' ),
        'required'    => true,
        'class'       => ['woocommerce-form-row--wide', 'form-row-wide'],
        'label_class' => ['woocommerce-form__label'],
    ], isset( $_POST['confirm_password'] ) ? wc_clean( $_POST['confirm_password'] ) : '' );
 
    foreach ($adm_registration_fields as $key => $field) {
        $args = [
            'type' => $field['type'],
            'label' => $field['label'],
            'required' => $field['required'],
            'class' => ['woocommerce-form-row--wide', 'form-row-wide'],
            'label_class' => ['woocommerce-form__label'],
        ];
        if ($field['type'] === 'select') {
            $args['options'] = $field['options'];
        }
        woocommerce_form_field($key, $args, isset($_POST[$key]) ? wc_clean($_POST[$key]) : '');
    }
}
add_action('woocommerce_register_form', 'dodaj_pola_rejestracji_woocommerce');



// // Walidacja NIP - przenieś tę funkcję globalnie
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
}

function walidacja_pol_formularza_rejestracji($errors, $username, $email) {
    // Jeśli rejestracja przez shortcode (brak pola password), pomiń walidację hasła
    $is_shortcode = !isset($_POST['password']) && isset($_POST['custom_registration_form_submitted']);

    if (!$is_shortcode) {
        if (empty($_POST['password'])) {
            $errors->add('password_error', __('Hasło jest wymagane.', 'storefront-child'));
        }
        if (isset($_POST['password'], $_POST['confirm_password']) && $_POST['password'] !== $_POST['confirm_password']) {
            $errors->add('password_mismatch', __('Hasła nie są takie same.', 'storefront-child'));
        }
    }

    if (empty($_POST['first_name'])) {
        $errors->add('first_name_error', __('Imię jest wymagane.', 'storefront-child'));
    }

    if (empty($_POST['billing_vat'])) {
        $errors->add('billing_vat_error', __('Numer NIP jest wymagany.', 'storefront-child'));
    } elseif (!waliduj_nip($_POST['billing_vat'])) {
        $errors->add('billing_vat_error', __('Podany numer NIP jest nieprawidłowy.', 'storefront-child'));
    }

    if (empty($_POST['billing_company'])) {
        $errors->add('billing_company_error', __('Nazwa firmy jest wymagana.', 'storefront-child'));
    }

    if (empty($_POST['billing_address_1'])) {
        $errors->add('billing_address_1_error', __('Adres firmy jest wymagany.', 'storefront-child'));
    }

    // if (empty($_POST['billing_phone'])) {
    //     $errors->add('billing_phone_error', __('Numer telefonu jest wymagany.', 'storefront-child'));
    // }

    if (empty($_POST['rodzaj_dzialalnosci'])) {
        $errors->add('rodzaj_dzialalnosci_error', __('Rodzaj działalności jest wymagany.', 'storefront-child'));
    }

    return $errors;
}
add_filter('woocommerce_registration_errors', 'walidacja_pol_formularza_rejestracji', 10, 3);





// Zapis danych klienta po rejestracji
function zapisz_dane_rejestracji_i_przypisz_role($customer_id) {

    wp_update_user(['ID' => $customer_id, 'role' => 'zainteresowany_oferta']);

    $woocommerce_fields = [
        'first_name',
        'last_name',
        'billing_company',
        'billing_address_1',
        'billing_address_2',
        'billing_phone',
        'billing_vat',
        'rodzaj_dzialalnosci',
        'company_additional_info'
    ];

    foreach ($woocommerce_fields as $field) {
        if (!empty($_POST[$field])) {
            update_user_meta($customer_id, $field, sanitize_text_field($_POST[$field]));
        }
    }
     update_user_meta($customer_id, "billing_first_name", sanitize_text_field($_POST["first_name"]) );
     update_user_meta($customer_id, "billing_last_name", sanitize_text_field($_POST["last_name"]) );

    if (!empty($_POST['password'])) {
        wp_set_password(sanitize_text_field($_POST['password']), $customer_id);
    }
}
add_action('woocommerce_created_customer', 'zapisz_dane_rejestracji_i_przypisz_role');



/*
// Przekierowanie po rejestracji
function przekieruj_po_rejestracji($redirect_to, $user) {
    if (in_array('zainteresowany_oferta', (array) $user->roles)) {
        return home_url('/dziękujemy-za-rejestrację/');
    
    }
    return $redirect_to;
}

add_filter('woocommerce_registration_redirect', 'przekieruj_po_rejestracji', 10, 2);
*/



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



function dodaj_inline_js_do_rejestracji() {
        ?>
        <script>
        document.addEventListener("DOMContentLoaded", function () {

        var pole = "#first_name";
        if( document.querySelector(pole) ){
            document.querySelector(pole).value="Aaaaaa";
        }

        var pole = "#last_name";
        if( document.querySelector(pole) ){
            document.querySelector(pole).value="Bbbbbbbb";
        }

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

        var pole = "#billing_address_2";
        if( document.querySelector(pole) ){
            document.querySelector(pole).value="adres linia 2";
        }

        var pole = "#rodzaj_dzialalnosci";
        if( document.querySelector(pole) ){
            document.querySelector(pole).value = "zdrowa-zywnosc";
        }

        var pole = "#billing_company";
        if( document.querySelector(pole) ){
            document.querySelector(pole).value = "firma billing company";
        }

        var pole = "#company_additional_info";
        if( document.querySelector(pole) ){
            document.querySelector(pole).value = "tam taram tamtam atmtaramatam atmraratm a";
        }

/*
        var pole = "#reg_password";
        if( document.querySelector(pole) ){
            document.querySelector(pole).value = "qweqwe";
        }
        var pole = "#confirm_password";
        if( document.querySelector(pole) ){
            document.querySelector(pole).value = "qweqwe";
        }
*/




        });
        </script>
        <?php
}
add_action('wp_footer', 'dodaj_inline_js_do_rejestracji');



// Shortcode: formularz rejestracji z tymi samymi polami co WooCommerce, bez hasła
add_shortcode('adm_registration_form_zainteresowany_oferta', function() {
    ob_start();
    if (is_user_logged_in()) {
        echo '<p>Jesteś już zalogowany.</p>';
        return ob_get_clean();
    }

    if (!empty($_POST['custom_registration_form_submitted'])) {
        $fields = [
            'first_name',
            'last_name',
            'email',
            'billing_company',
            'billing_address_1',
            'billing_address_2',
            'billing_phone',
            'billing_vat',
            'rodzaj_dzialalnosci',
            'company_additional_info'
        ];

        $errors = new WP_Error();
        // Użyj tej samej walidacji co WooCommerce
        $errors = apply_filters('woocommerce_registration_errors', $errors, $_POST['email'] ?? '', $_POST['email'] ?? '');

        if (empty($errors->errors)) {
            $first_name = sanitize_text_field($_POST['first_name'] ?? '');
            $last_name = sanitize_text_field($_POST['last_name'] ?? '');
            $email = sanitize_email($_POST['email'] ?? '');
            // Generowanie unikalnego loginu na podstawie e-maila
            $username = strstr($email, '@', true);
            $base_username = $username;
            $try = 0;
            while (username_exists($username) && $try < 90) {
                $rand = str_pad(strval(rand(0,99)), 2, '0', STR_PAD_LEFT);
                $username = $base_username . $rand;
                $try++;
            }
            $password = wp_generate_password(12, true);
            $user_id = wp_create_user($username, $password, $email);
            if (!is_wp_error($user_id)) {
                wp_update_user([
                    'ID' => $user_id,
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'role' => 'zainteresowany_oferta'
                ]);
                foreach ($fields as $field) {
                    if (!empty($_POST[$field])) {
                        update_user_meta($user_id, $field, sanitize_text_field($_POST[$field]));
                    }
                }
                update_user_meta($user_id, 'billing_first_name', $first_name);
                update_user_meta($user_id, 'billing_last_name', $last_name);

                // $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
                // $mail_content = "Witaj $first_name $last_name,\n\nTwoje konto w sklepie $blogname zostało utworzone.\n\n";
                // if ( function_exists( 'generate_password_reset_link' ) ) {
                //     $mail_content .= "Aby ustawić swoje hasło, kliknij w poniższy link:\n". generate_password_reset_link($email) ."\n\n";
                // }
                // $mailer = WC()->mailer();
                // $wrapped_message = $mailer->wrap_message('Twoje konto zostało utworzone', nl2br($mail_content));
                // $headers = ['Content-Type: text/html; charset=UTF-8'];
                // wp_mail($email, 'Twoje konto w sklepie ' . $blogname, $wrapped_message, $headers);

                do_action('woocommerce_created_customer', $user_id);

                echo '<p style="color:green;">Sukces! Niebawem wyślemy do Ciebie ofertę hurtową.</p>';
                return ob_get_clean();
            } else {
                echo '<p style="color:red;">Błąd rejestracji: ' . esc_html($user_id->get_error_message()) . '</p>';
                // NIE return! Wyświetl formularz ponownie z danymi
            }
        } else {
            foreach ($errors->get_error_messages() as $error) {
                echo '<p style="color:red;">' . esc_html($error) . '</p>';
            }
        }
    }

    ?>
    <form id="adm--custom-registration-form" method="post" action="">
        <p>
            <label for="first_name">Imię</label><br>
            <input type="text" id="first_name" name="first_name" required value="<?php echo esc_attr($_POST['first_name'] ?? ''); ?>">
        </p>
        <p>
            <label for="last_name">Nazwisko</label><br>
            <input type="text" id="last_name" name="last_name" value="<?php echo esc_attr($_POST['last_name'] ?? ''); ?>">
        </p>
        <p>
            <label for="email">Adres e-mail</label><br>
            <input type="email" id="email" name="email" required value="<?php echo esc_attr($_POST['email'] ?? ''); ?>">
        </p>
        <p>
            <label for="billing_company">Nazwa firmy</label><br>
            <input type="text" id="billing_company" name="billing_company" required value="<?php echo esc_attr($_POST['billing_company'] ?? ''); ?>">
        </p>
        <p>
            <label for="billing_vat">NIP</label><br>
            <input type="text" id="billing_vat" name="billing_vat" required value="<?php echo esc_attr($_POST['billing_vat'] ?? ''); ?>">
        </p>
        <p>
            <label for="billing_address_1">Adres firmy</label><br>
            <input type="text" id="billing_address_1" name="billing_address_1" required value="<?php echo esc_attr($_POST['billing_address_1'] ?? ''); ?>">
        </p>
        <p>
            <label for="billing_address_2">Adres c.d.</label><br>
            <input type="text" id="billing_address_2" name="billing_address_2" value="<?php echo esc_attr($_POST['billing_address_2'] ?? ''); ?>">
        </p>
        <p>
            <label for="billing_phone">Numer telefonu (nieobowiązkowo)</label><br>
            <input type="text" id="billing_phone" name="billing_phone" value="<?php echo esc_attr($_POST['billing_phone'] ?? ''); ?>">
        </p>
        <p>
            <label for="company_additional_info">Dodatkowe informacje o firmie</label><br>
            <textarea id="company_additional_info" name="company_additional_info"><?php echo esc_textarea($_POST['company_additional_info'] ?? ''); ?></textarea>
        </p>
        <p>
            <label for="rodzaj_dzialalnosci">Rodzaj działalności</label><br>
            <select id="rodzaj_dzialalnosci" name="rodzaj_dzialalnosci" required>
                <option value="">Wybierz rodzaj działalności</option>
                <option value="sklep-internetowy" <?php selected($_POST['rodzaj_dzialalnosci'] ?? '', 'sklep-internetowy'); ?>>Sklep internetowy</option>
                <option value="sklep-spozywczy" <?php selected($_POST['rodzaj_dzialalnosci'] ?? '', 'sklep-spozywczy'); ?>>Sklep spożywczy</option>
                <option value="zdrowa-zywnosc" <?php selected($_POST['rodzaj_dzialalnosci'] ?? '', 'zdrowa-zywnosc'); ?>>Sklep ze zdrową żywnością</option>
                <option value="kawa-herbata" <?php selected($_POST['rodzaj_dzialalnosci'] ?? '', 'kawa-herbata'); ?>>Sklep z kawą/herbatą</option>
                <option value="gastronomia" <?php selected($_POST['rodzaj_dzialalnosci'] ?? '', 'gastronomia'); ?>>Lokal gastronomiczny</option>
                <option value="hurtownia" <?php selected($_POST['rodzaj_dzialalnosci'] ?? '', 'hurtownia'); ?>>Hurtownia</option>
                <option value="inny" <?php selected($_POST['rodzaj_dzialalnosci'] ?? '', 'inny'); ?>>Inny</option>
            </select>
        </p>
        <input type="hidden" name="custom_registration_form_submitted" value="1">
        <p>
            <button type="submit">Zarejestruj</button>
        </p>
    </form>
    <?php

    return ob_get_clean();
});


