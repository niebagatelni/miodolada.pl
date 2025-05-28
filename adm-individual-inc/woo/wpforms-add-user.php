<?php

add_action('wpforms_process_complete', 'adm_wpforms_create_customer', 10, 4);

function adm_wpforms_create_customer($fields, $entry, $form_data, $entry_id) {


    if ((int)$form_data['id'] !== 58) {
        return;
    }

    $data = array();

    foreach ($fields as $field) {
	$type = strtolower(trim($field['type']));
        $label = strtolower(trim($field['name']));
        $value = $field['value'] ?? '';



        if ( $type === "text" )   {  $value = sanitize_text_field($value);  }
        if ( $type === "textarea") {  $value = sanitize_textarea_field($value);  }
        if ( $type === "number" ) {  $value = filter_var($value, FILTER_SANITIZE_NUMBER_INT);  }

	

		// Do formularza 'Zapytanie o ofertę'

        	if ($type === 'email' && is_email($value)) {
        	    $data['email'] = sanitize_email($value);

        	}elseif (strpos(strtolower($label), 'numer telefonu') !== false) {
                    $data['fields']['phone'] = filter_var($value, FILTER_SANITIZE_NUMBER_INT);

                } elseif(strpos(strtolower($label), 'nazwa firmy') !== false) {
                    $data['fields']['company'] = $value;

                } elseif (strpos(strtolower($label), 'nip') !== false) {
                    $data['fields']['nip'] = $value;

                } elseif (strpos(strtolower($label), 'nazwisko') !== false) {
                    $data['fields']['last_name'] = $value;

                } elseif (strpos(strtolower($label), 'imię') !== false || strpos(strtolower($label), 'imie') !== false) {
                    $data['name'] = $data['fields']['name'] = $value;

                } elseif (strpos(strtolower($label), 'informacje') !== false && $type === "textarea") {
	            $data['fields']['customer_note'] = $value;
		}

		$data['fields']['zrodlo'] = "miodolada-hurt";



    }


 file_put_contents(ABSPATH . 'fields.json', json_encode($fields, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
 file_put_contents(ABSPATH . 'data.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));


	if ( !empty($data['email']) && !empty($data['name']) ) {
		 include ADM_THEME_DIR.'/adm-inc/woo/wpforms-add-ml-subscriber.php';
	}



// ----------------------------------------------
// TWORZENIE UŻYTKOWNIKA, GDY NIE ISTNIEJE


    if (empty($data['email'])) {
	adm_log2("Brak emaila w `wpforms-add-user`");
        return;
    }

    if (email_exists($data['email'])) {
        return;
    }

    // Generowanie bazowej nazwy użytkownika
    $base_username = sanitize_user(
        (!empty($data['name']) && !empty($data['fields']['last_name']))
            ? strtolower($data['name'] . '.' . $data['fields']['last_name'])
            : current(explode('@', $data['email']))
    );



    // Sprawdzenie unikalności nazwy użytkownika z pętlą
    $attempt = 0;
    $username = $base_username;
    do {
        if ($attempt > 0) {
            $random_number = rand(100, 999);
            $username = $base_username . $random_number;
        }
        $attempt++;
    } while (username_exists($username) && $attempt <= 50);

    if (username_exists($username)) {
	adm_log2("Nazwa użytkownika nadal istnieje `wpforms-add-user`");
        return;
    }


    $password = wp_generate_password(12, true);
    $user_id = wp_create_user($username, $password, $data['email']);

	$bledy = adm__get_wp_error( $user_id );
	
	if ( $bledy !== '' ) {
	    adm_log2( "Błędy z user_id w `wpforms-add-user`:" . nl2br(esc_html($bledy)) );
	    return;
	}



    // Dodanie użytkownika

    $user = new WP_User($user_id);
    $user->set_role('zainteresowany_oferta');

    update_user_meta($user_id, 'billing_email', $data['email'] ?? '');
    update_user_meta($user_id, 'billing_phone', $data['fields']['phone'] ?? '');
    update_user_meta($user_id, 'billing_company', $data['fields']['company'] ?? '');
    update_user_meta($user_id, 'billing_vat', $data['fields']['nip'] ?? '');
    update_user_meta($user_id, 'customer_note', $data['fields']['customer_note'] ?? '');
    update_user_meta($user_id, 'first_name', $data['name'] ?? '');
    update_user_meta($user_id, 'last_name', $data['fields']['last_name'] ?? 'brakkk');

adm_notify_new_customer($user_id);


// <-- TWORZENIE UŻYTKOWNIKA, GDY NIE ISTNIEJE
// ----------------------------------------------



//*/


} // <-- function adm_wpforms_create_customer
