<?php


add_filter( 'wp_new_user_notification_email', 'adm__disable_notify_for_new_zainteresowany_oferta', 10, 3 );
function adm__disable_notify_for_new_zainteresowany_oferta( $email, $user, $blogname ) {
    if ( in_array( 'zainteresowany_oferta', (array) $user->roles ) ) {
        // Zwraca pustą zawartość, aby zablokować e-mail
        return [
            'to'      => '',
            'subject' => '',
            'message' => '',
            'headers' => '',
        ];
    }

    return $email;
}


// Wysyłąnie maila do administratora po rejestracji klienta

add_action('woocommerce_created_customer', 'adm__notify_new_customer');
function adm__notify_new_customer($user_id) {

	$roles_in = array('zainteresowany_oferta');
	$roles_ex = array('shop_manager', 'administrator');

	$recipients = [
	    //'sklep@pachniczowka.pl',
	    'adnauczyciel@gmail.com'
	];


	if (!isset($roles_in) || !isset($roles_ex) || !isset($recipients)) adm_log2("Błędy w rolach i odbiorcach (nie istnieją)");
	if ( empty($roles_in) || empty($roles_ex) || empty($recipients) ) adm_log2("Błędy w rolach i odbiorcach (są puste)");


	$user = get_userdata($user_id);
	if ( !isset($user) || !is_object($user)) {
		adm_log2("Nie znaleziono użytkownika o ID: $user_id");
		return;
	}

	$user_roles = (array) $user->roles;

	$allowed = false;
	foreach ($user_roles as $role) {
		if (in_array($role, $roles_ex)) {
			return;
		}
		if (in_array($role, $roles_in)) {
			$allowed = true;
		}
	}

	$user_email = (!empty($user->user_email) && filter_var($user->user_email, FILTER_VALIDATE_EMAIL)) 
					? sanitize_email($user->user_email) 
					: 'artur.dlugosz@pachniczowka.pl';

	$user_name = !empty($user->first_name) ? esc_html($user->first_name) : '';


	$blogname   = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);


    // ----------------------------------------
    // Budowa treści maila do admina
	$reply_email = $user_email;

	$subject = 'Nowy klient zarejestrowany w sklepie';

	$message_lines = [
		"Nowy klient zainteresowany ofertą hurtową:",
		"Email: " . $user_email,
		"Nazwa firmy: " . get_user_meta($user_id, 'billing_company', true),
		"Rodzaj działalności: " . get_user_meta($user_id, 'rodzaj_dzialalnosci', true),
		"NIP: " . get_user_meta($user_id, 'billing_tax_no', true),
		"Imię: " . $user_name,
		"Adres: " . trim(get_user_meta($user_id, 'billing_address_1', true) . ' ' . get_user_meta($user_id, 'billing_address_2', true)),
		"Telefon: " . get_user_meta($user_id, 'billing_phone', true)
	];


	// Gneerowanie linku do zmiany roli
	$token = wp_generate_password(20, false);
	$expires = time() + 3 * 24 * 3600; // 3 dni
	update_user_meta($user_id, '_role_change_token', $token);
	update_user_meta($user_id, '_role_change_token_expires', $expires);
	$role_link = add_query_arg([
		'change_role' => 1,
		'token' => $token
	], home_url('/'));

	$message_lines[] = '';
	$message_lines[] = 'Aby zaakceptować klienta, kliknij w poniższy link:';
	$message_lines[] = esc_url($role_link);
	$message_lines[] = '(Link ważny 3 dni, jednorazowy)';

	$message = implode("\n", $message_lines);

	$mailer = WC()->mailer();
	$html = $mailer->wrap_message($subject, nl2br($message));
	$headers = [
		'Content-Type: text/html; charset=UTF-8',
		'Reply-To: ' . $reply_email
	];
	foreach ($recipients as $email) {
		$sent = $mailer->send($email, $subject, $html, $headers);
		if (!$sent) {
			adm_log2("Nie udało się wysłać powiadomienia rejestracyjnego:\n - user ID: $user_id\n - email: $email");
		}
	}



	

	// ----------------------------------------
	// Budowa treści maila do klienta

	$reply_email = 'sklep@pachniczowka.pl';
	$subject = 'Twoje konto zostało utworzone';
	$message_html = '
		<p>Witaj <strong>' . $user_name. '</strong>,</p>
		<p>Twoje konto w hurtowni <a href="https://miodolada.pl"><strong>miodolada.pl</strong></a> zostało pomyślnie utworzone.</p>
		<p>Gdy uporamy się z pracą na pasiece, przygotujemy dla Ciebie ofertę hurtową.<br>Wtedy w hurtowni, po zalogowaniu się, zobaczysz ceny produktów i będzie można dokonać pierwszego zakupu.</p>';

		$message_html .= '<p>Życzymy słodkiego dnia :) </p>';

	$mailer = WC()->mailer();
	$wrapped_message = $mailer->wrap_message($subject, $message_html);
	$headers = [
		'Content-Type: text/html; charset=UTF-8',
		'Reply-To: ' . $reply_email
	];

	$sent = $mailer->send($user_email, $subject, $wrapped_message, $headers);

		if (!$sent) {
			adm_log2("Nie udało się wysłać powiadomienia rejestracyjnego do klienta:\n - user ID: $user_id\n - email: $user_email");

			wp_mail(
				get_option('admin_email'),
				'Błąd wysyłki e-maila (rejestracja użytkownika)',
				'Nie udało się wysłać wiadomości do użytkownika: ' . $user_email,
				['Content-Type: text/plain; charset=UTF-8']
			);

			adm_log2("Błąd wysyłki e-maila do klienta: $user_email");
		}




} // <-- adm__notify_new_customer



// Wysyłka maila do klienta po zmianie roli na klient_hurtowy
add_action('adm_role_changed_to_klient_hurtowy', 'adm__notify_customer_role_activated', 10, 1);
function adm__notify_customer_role_activated($user_id) {
 
	if (get_user_meta($user_id, '_hurt_mail_sent', true)) {
        return;
    }
	
	$user = get_userdata($user_id);
    if (!$user || !is_object($user)) return;
    $user_email = (!empty($user->user_email) && filter_var($user->user_email, FILTER_VALIDATE_EMAIL))
        ? sanitize_email($user->user_email)
        : '';
    if (empty($user_email)) return;
    $user_name = !empty($user->first_name) ? esc_html($user->first_name) : '';
    $reply_email = 'sklep@pachniczowka.pl';
    $subject = 'Twoje konto zostało aktywowane';
    $message_html = '
        <p>Witaj <strong>' . $user_name. '</strong>,</p>
        <p>Twoje konto w hurtowni <a href="https://miodolada.pl"><strong>miodolada.pl</strong></a> zostało aktywowane.<br>
        Masz już dostęp do cen hurtowych i możesz dokonać pierwszego zakupu.</p>';
    
		if(function_exists('generate_password_reset_link')) {
			$reset_url = generate_password_reset_link($user_email);
			if ($reset_url) {
				$message_html .= '<p>Jeśli chcesz ustawić lub zmienić swoje hasło, kliknij tutaj: <a href="' . esc_url($reset_url) . '"> Ustaw hasło </a></p>';
			}else{
				adm_log2("Nie udało się wygenerować linku do resetu hasła dla użytkownika: $user_email (link nie wygenerowany)");	
			}
		}else{
			adm_log2("Nie udało się wygenerować linku do resetu hasła dla użytkownika: $user_email (funkcja generate_password_reset_link nie istnieje)");
		}
		
    $message_html .= '<p>Dziękujemy słodko :) </p>';
    $mailer = WC()->mailer();
    $wrapped_message = $mailer->wrap_message($subject, $message_html);
    $headers = [
        'Content-Type: text/html; charset=UTF-8',
        'Reply-To: ' . $reply_email
    ];
    $sent = $mailer->send($user_email, $subject, $wrapped_message, $headers);
    if (!$sent && function_exists('adm_log2')) {
        adm_log2("Nie udało się wysłać powiadomienia o aktywacji konta do klienta: $user_email");
    }
	if ($sent) {
        update_user_meta($user_id, '_hurt_mail_sent', 1);
    }
    return $sent; // Zwraca true/false w zależności od powodzenia wysyłki
}



// Automatyczna wysyłka maila do klienta po każdej zmianie roli na klient_hurtowy

add_action('set_user_role', function($user_id, $new_role, $old_roles) {
    if ($new_role === 'klient_hurtowy') {
        adm__notify_customer_role_activated($user_id);
    }
}, 10, 3);

add_action('user_role_changed', function($user_id, $role, $old_roles) {
    if ($role === 'klient_hurtowy') {
        adm__notify_customer_role_activated($user_id);
    }
}, 10, 3);

add_action('profile_update', function($user_id, $old_user_data) {
    $user = get_userdata($user_id);
    if (in_array('klient_hurtowy', (array)$user->roles) && !in_array('klient_hurtowy', (array)$old_user_data->roles)) {
        adm__notify_customer_role_activated($user_id);
    }
}, 10, 2);

add_action('add_user_role', function($user_id, $role) {
    if ($role === 'klient_hurtowy') {
        adm__notify_customer_role_activated($user_id);
    }
}, 10, 2);


