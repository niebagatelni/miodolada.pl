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
    }
}



// // Wyłącz domyślny e-mail WooCommerce o utworzeniu konta TYLKO podczas rejestracji przez ten shortcode
// add_filter('woocommerce_email_enabled_customer_new_account', function($enabled) {
//     if (isset($_POST['custom_registration_form_submitted'])) {
//         return false;
//     }
//     return $enabled;
// });


// Wyłącz domyślny e-mail WooCommerce o utworzeniu konta
add_filter('woocommerce_email_enabled_customer_new_account', function($enabled) {
    return false;
});


// Wspólna definicja pól rejestracji
global $adm_registration_fields;
$adm_registration_fields = [
    'first_name' => [
        'type' => 'text',
        'label' => __( 'Imię', 'storefront-child' ),
        'required' => true,
    ],
    'billing_tax_no' => [
        'type' => 'text',
        'label' => __( 'NIP', 'storefront-child' ),
        'required' => true,
    ],
    'email' => [
        'type' => 'email',
        'label' => __( 'Adres e-mail', 'storefront-child' ),
        'required' => true,
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

];

/*
// Dodaję dodatkowe pola billingowe do WooCommerce (dla Fakturowni)
add_filter('woocommerce_billing_fields', function($fields) {
    $fields['_billing_company_name'] = array(
        'label'       => __('Nazwa firmy', 'storefront-child'),
        'placeholder' => _x('Nazwa firmy', 'placeholder', 'storefront-child'),
        'required'    => false,
        'class'       => array('form-row-wide'),
        'priority'    => 21,
    );
    $fields['_billing_tax_no'] = array(
        'label'       => __('NIP', 'storefront-child'),
        'placeholder' => _x('NIP', 'placeholder', 'storefront-child'),
        'required'    => false,
        'class'       => array('form-row-wide'),
        'priority'    => 22,
    );
    $fields['_billing_company_address'] = array(
        'label'       => __('Ulica firmy', 'storefront-child'),
        'placeholder' => _x('Ulica firmy', 'placeholder', 'storefront-child'),
        'required'    => false,
        'class'       => array('form-row-wide'),
        'priority'    => 23,
    );
    $fields['_billing_company_postcode'] = array(
        'label'       => __('Kod pocztowy firmy', 'storefront-child'),
        'placeholder' => _x('Kod pocztowy firmy', 'placeholder', 'storefront-child'),
        'required'    => false,
        'class'       => array('form-row-wide'),
        'priority'    => 24,
    );
    $fields['_billing_company_city'] = array(
        'label'       => __('Miasto firmy', 'storefront-child'),
        'placeholder' => _x('Miasto firmy', 'placeholder', 'storefront-child'),
        'required'    => false,
        'class'       => array('form-row-wide'),
        'priority'    => 25,
    );
    return $fields;
});
*/


// Formularz WooCommerce (na stronie MojeKonto)
function dodaj_pola_rejestracji_woocommerce() {
    global $adm_registration_fields;

    woocommerce_form_field( 'confirm_password', [
        'type'        => 'password',
        'label'       => __( 'Potwierdź hasło', 'storefront-child' ),
        'required'    => true,
    ], isset( $_POST['confirm_password'] ) ? wc_clean( $_POST['confirm_password'] ) : '' );

    foreach ($adm_registration_fields as $key => $field) {
        if ($key === 'email') continue;
        $label = $field['label'];
        if (empty($field['required'])) {
            $label .= ' <span class="label-note">(nieobowiązkowo)</span>';
        }
        $args = [
            'type' => $field['type'],
            'label' => $label,
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


function walidacja_pol_formularza_rejestracji($errors, $username, $email) {

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

    if (empty($_POST['billing_tax_no'])) {
        $errors->add('billing_tax_no_error', __('Numer NIP jest wymagany.', 'storefront-child'));
    } elseif (!waliduj_nip($_POST['billing_tax_no'])) {
        $errors->add('billing_tax_no_error', __('Podany numer NIP jest nieprawidłowy.', 'storefront-child'));
    }

    if (empty($_POST['email'])) {
        $errors->add('email_error', __('Adres e-mail jest wymagany.', 'storefront-child'));
    } elseif (!is_email($_POST['email'])) {
        $errors->add('email_error', __('Podany adres e-mail jest nieprawidłowy.', 'storefront-child'));
    }

    return $errors;
}
add_filter('woocommerce_registration_errors', 'walidacja_pol_formularza_rejestracji', 10, 3);





// Zapis danych klienta po rejestracji
function zapisz_dane_rejestracji_i_przypisz_role($customer_id) {
    global $adm_registration_fields;
    wp_update_user(['ID' => $customer_id, 'role' => 'zainteresowany_oferta']);
    
    $fields = array_keys($adm_registration_fields);

    foreach ($fields as $field) {
        if (!empty($_POST[$field])) {
            update_user_meta($customer_id, $field, sanitize_text_field($_POST[$field]));
        }
    }
    update_user_meta($customer_id, "billing_first_name", sanitize_text_field($_POST["first_name"] ?? ''));

    // Ustawianie hasła
    if (!empty($_POST['password'])) {
        wp_set_password(sanitize_text_field($_POST['password']), $customer_id);
    }
}
add_action('woocommerce_created_customer', 'zapisz_dane_rejestracji_i_przypisz_role');


/*
// Przekierowanie po rejestracji
function przekieruj_po_rejestracji($redirect_to, $user) {
    if (in_array('zainteresowany_oferta', (array) $user->roles)) {
        return home_url();
    }
    return $redirect_to;
}

add_filter('woocommerce_registration_redirect', 'przekieruj_po_rejestracji', 100);
*/



// Inline JS do automatycznego wypełniania formularza (np. do testów)
function dodaj_inline_js_do_rejestracji() {
    if( is_front_page() || is_home() || ( is_account_page() && !is_user_logged_in() ) ){
        ?>
        <script>
        document.addEventListener("DOMContentLoaded", function () {
            const pola = {
                '#first_name': "Aaaaaa",
                '#billing_phone': "654654654",
                '#billing_tax_no': "9462600874",
                '#company_additional_info': "tam taram tamtam atmtaramatam atmraratm a",
                '#reg_password': "qweqwe",
                '#confirm_password': "qweqwe"
            };

            Object.entries(pola).forEach(([selector, value]) => {
                const el = document.querySelector(selector);
                if (el) el.value = value;
            });
        });
        </script>
        <?php
    }
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
        global $adm_registration_fields;
        $fields = array_keys($adm_registration_fields);
        $errors = new WP_Error();
        $errors = apply_filters('woocommerce_registration_errors', $errors, $_POST['email'] ?? '', $_POST['email'] ?? '');

        if (empty($errors->errors)) {
            $first_name = sanitize_text_field($_POST['first_name'] ?? '');
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
                    'role' => 'zainteresowany_oferta'
                ]);
                foreach ($fields as $field) {
                    if (!empty($_POST[$field])) {
                        update_user_meta($user_id, $field, sanitize_text_field($_POST[$field]));
                    }
                }
                update_user_meta($user_id, 'billing_first_name', $first_name);

                do_action('woocommerce_created_customer', $user_id);

                echo '<p class="adm-success-message" style="text-align: center; color:green;">Sukces! Niebawem wyślemy do Ciebie ofertę hurtową.</p>';
                return ob_get_clean();
            } else {
                echo '<p class="adm-failure-message" style="color:red;">Błąd rejestracji: ' . esc_html($user_id->get_error_message()) . '</p>';
            }
        } else {
            foreach ($errors->get_error_messages() as $error) {
                echo '<p class="adm-failure-message" style="text-align: center; color:red;">' . esc_html($error) . '</p>';
            }
        }
    }
    ?>
    <form id="adm--custom-registration-form" method="post" action="#main-zaoferuj">
        <?php
        global $adm_registration_fields;
        foreach ($adm_registration_fields as $key => $field) {
            $value = isset($_POST[$key]) ? $_POST[$key] : '';
            $required = $field['required'] ? 'required' : '';
            $label = $field['label'];
            if (empty($field['required'])) {
                $label .= ' <span class="label-note">(nieobowiązkowo)</span>';
            }
            if ($field['type'] === 'textarea') {
                echo '<p><label for="' . esc_html($key) . '">' . $label . '</label>';
                echo '<textarea id="' . esc_attr($key) . '" name="' . esc_attr($key) . '" ' . $required . '>' . esc_textarea($value) . '</textarea></p>';
            } elseif ($field['type'] === 'url') {
                echo '<p><label for="' . esc_html($key) . '">' . $label . '</label>';
                echo '<input type="url" id="' . esc_attr($key) . '" name="' . esc_attr($key) . '" ' . $required . ' value="' . esc_url($value) . '" /></p>';
            } elseif ($field['type'] === 'html') {
                echo '<p><label>' . $label . '</label>';
                echo esc_html($value) . '</p>';
            } else {
                echo '<p><label for="' . esc_html($key) . '">' . $label . '</label>';
                echo '<input type="' . esc_attr($field['type']) . '" id="' . esc_attr($key) . '" name="' . esc_attr($key) . '" ' . $required . ' value="' . esc_attr($value) . '" /></p>';
            }
        }
        ?>
        <input type="hidden" name="custom_registration_form_submitted" value="1">
        <p class="form-footer">Twoje dane zbieramy tylko po to, by złożyć Ci najsłodszą pod słońcem ofertę hurtową.</p>
        <p>
            <button type="submit">Zarejestruj</button>
        </p>
    </form>
    <?php
    return ob_get_clean();
});




// ---------------------------------------------------------------------
// Dodatkowe pola użytkownika w panelu admina
//

function wyswietl_pola_profilu_uzytkownika($user) {
    if (!current_user_can('edit_user', $user->ID)) {
        return;
    }

    // Dodatkowe informacje o firmie (nie będące standardowymi polami billingowymi WooCommerce)
    $extra_fields = [
        'billing_tax_no'          => 'NIP',
        'company_additional_info' => 'Dodatkowe informacje o firmie',
    ];

    echo '<h3>Dodatkowe informacje o firmie</h3>';
    echo '<table class="form-table">';

    foreach ($extra_fields as $field => $label) {
        $value = get_user_meta($user->ID, $field, true);
        echo '<tr>';
        echo '<th><label for="' . esc_attr($field) . '">' . esc_html($label) . '</label></th>';
        echo '<td>';
        if ($field === 'company_additional_info') {
            echo '<textarea name="' . esc_attr($field) . '" id="' . esc_attr($field) . '" rows="5" class="regular-text">' . esc_textarea($value) . '</textarea>';
        } else {
            echo '<input type="text" name="' . esc_attr($field) . '" id="' . esc_attr($field) . '" value="' . esc_attr($value) . '" class="regular-text" />';
        }
        echo '</td>';
        echo '</tr>';
    }
    echo '</table>';
}

// Dodaj pole do panelu admina (edycja zamówienia)
// add_filter('woocommerce_admin_billing_fields', function ($fields) {
//     $fields['billing_tax_no'] = __('NIP', 'woocommerce');
//     return $fields;
// });

// Zapisywanie danych z profilu użytkownika
function zapisz_pola_profilu_uzytkownika($user_id) {
    if (!current_user_can('edit_user', $user_id)) {
        return false;
    }

    // Zapisujemy tylko niestandardowe pola, które nie są billingowymi WooCommerce
    $fields = [
        'billing_tax_no',
        'company_additional_info',
    ];

    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            $value = ($field === 'company_additional_info')
                ? sanitize_textarea_field($_POST[$field])
                : sanitize_text_field($_POST[$field]);
            update_user_meta($user_id, $field, $value);
        }
    }
}








// Hooki do WordPressa
add_action('show_user_profile', 'wyswietl_pola_profilu_uzytkownika');
add_action('edit_user_profile', 'wyswietl_pola_profilu_uzytkownika');
add_action('personal_options_update', 'zapisz_pola_profilu_uzytkownika');
add_action('edit_user_profile_update', 'zapisz_pola_profilu_uzytkownika');





// ---------------------------------------------------------------------
// Dodatkowe pola użytkownika w panelu klienta "Moje konto"
//

add_action('woocommerce_edit_account_form', function() {
    $user_id = get_current_user_id();
    ?>
    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
        <label for="company_additional_info"><?php _e('Dodatkowe informacje o firmie', 'storefront-child'); ?></label>
        <textarea class="woocommerce-Input" name="company_additional_info" id="company_additional_info"><?php echo esc_textarea(get_user_meta($user_id, 'company_additional_info', true)); ?></textarea>
    </p>
    <?php
});
add_action('woocommerce_save_account_details', function($user_id) {
    if (isset($_POST['company_additional_info'])) {
        $value = sanitize_textarea_field($_POST['company_additional_info']);
        update_user_meta($user_id, 'company_additional_info', $value);
    }
});

// --- Wyświetlanie dodatkowych informacji na dashboardzie "Moje konto" ---
add_action('woocommerce_account_dashboard', function() {
    $user_id = get_current_user_id();
    $info = get_user_meta($user_id, 'company_additional_info', true);
    if (!empty($info)) {
        echo '<h3>' . __('Dodatkowe informacje o firmie', 'storefront-child') . '</h3>';
        echo '<table class="woocommerce-table shop_table">';
        if (!empty($info)) {
            echo '<tr><th>' . __('Dodatkowe informacje', 'storefront-child') . '</th><td>' . esc_html($info) . '</td></tr>';
        }
        echo '</table>';
    }
});



