<?php


add_action('woocommerce_created_customer', 'adm_notify_new_customer');

function adm_notify_new_customer($user_id) {


adm_log3("111");


	$roles_in = array('customer', 'subscriber', 'zainteresowany_oferta');
	$roles_ex = array('shop_manager', 'administrator');

	$recipients = [
	    //'sklep@pachniczowka.pl',
	    'artur.dlugosz@pachniczowka.pl',
	    'adnauczyciel@gmail.com'
	];


if (!isset($roles_in) || !isset($roles_ex) || !isset($recipients)) adm_log2("customer-registration-notification: Błędy w rolach i odbiorcach (nie istnieją)");
if ( empty($roles_in) || empty($roles_ex) || empty($recipients) ) adm_log2("customer-registration-notification: Błędy w rolach i odbiorcach (są puste)");


adm_log3("222");

	$user = get_userdata($user_id);
	if ( !isset($user) || !is_object($user)) {
		adm_log2("customer-registration-notification: Nie znaleziono użytkownika o ID: $user_id");
		return;
	}

	$user_roles = (array) $user->roles;
adm_log3("333");

	$allowed = false;
	foreach ($user_roles as $role) {
		if (in_array($role, $roles_ex)) {
			return;
		}
		if (in_array($role, $roles_in)) {
			$allowed = true;
		}
	}

	if (!$allowed) {
		adm_log2("Użytkownik ID $user_id nie posiada żadnej z dozwolonych ról. Powiadomienie NIE zostanie wysłane.");
		return;
	}
adm_log3("444");


	$user_email = (!empty($user->user_email) && filter_var($user->user_email, FILTER_VALIDATE_EMAIL)) 
                    ? sanitize_email($user->user_email) 
                    : 'artur.dlugosz@pachniczowka.pl';

	$user_name = !empty($user->first_name) ? esc_html($user->first_name) : '';


	$blogname   = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

adm_log3("555");

    // ----------------------------------------
    // Budowa treści maila do admina

	$reply_email = $user_email;

	$subject = 'Nowy klient zarejestrowany w sklepie';

	$message_lines = [
	    "Nowy klient zainteresowany ofertą hurtową:",
	    "Email: " . $user_email,
	    "Nazwa firmy: " . get_user_meta($user_id, 'billing_company', true),
	    "Rodzaj działalności: " . get_user_meta($user_id, 'rodzaj_dzialalnosci', true),
	    "NIP: " . get_user_meta($user_id, 'billing_vat', true),
	    "Imię: " . $user_name,
	    "Adres: " . trim(get_user_meta($user_id, 'billing_address_1', true) . ' ' . get_user_meta($user_id, 'billing_address_2', true)),
	    "Telefon: " . get_user_meta($user_id, 'billing_phone', true)
	];

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
	        adm_log2("customer-registration-notification: Nie udało się wysłać powiadomienia rejestracyjnego:\n - user ID: $user_id\n - email: $email");
	    }
	}

adm_log3("666");

    // ----------------------------------------
    // Budowa treści maila do klienta

	$reply_email = 'sklep@pachniczowka.pl';


	$subject = 'Twoje konto zostało utworzone';

	$message_html = '
	    <p>Witaj <strong>' . $user_name. '</strong>,</p>
	    <p>Twoje konto w hurtowni <a href="https://miodolada.pl"><strong>miodolada.pl</strong></a> zostało pomyślnie utworzone.</p>
	    <p>Gdy uporamy się z pracą na pasiece, przygotujemy dla Ciebie ofertę hurtową.</p>
	';



	if ( function_exists( 'generate_password_reset_link' ) ) {
		if( $reset_link = generate_password_reset_link($user_email) ) {
			$message_html .= '<p>Możesz już zalogować się w hurtowni. Kliknij w poniższy link, aby ustawić swoje hasło:</p>';
			$message_html .= '<p><a href="' . esc_url($reset_link) . '" style="background-color:#96588a;color:#fff;padding:12px 24px;text-decoration:none;border-radius:4px;display:inline-block;">Ustaw hasło</a></p>';
        	} else {
        		adm_log2("[customer-notification-registration] Link do resetu hasła jest niepoprawny");
        	}
	}else{
		adm_log2("[customer-notification-registration] Brakuje funkcji generowania linku do resetu hasła");
	}
	

	$message_html .= '<p>Dziękujemy,<br>Zespół ' . esc_html($blogname) . '</p>';



	$mailer = WC()->mailer();
	$wrapped_message = $mailer->wrap_message($subject, $message_html);
	$headers = [
	    'Content-Type: text/html; charset=UTF-8',
	    'Reply-To: ' . $reply_email
	];

	$sent = $mailer->send($user_email, $subject, $wrapped_message, $headers);

	    if (!$sent) {
	        adm_log2("[customer-registration-notification]: Nie udało się wysłać powiadomienia rejestracyjnego do klienta:\n - user ID: $user_id\n - email: $user_email");

	    	wp_mail(
	    	    get_option('admin_email'),
	    	    'Błąd wysyłki e-maila (rejestracja użytkownika)',
	    	    'Nie udało się wysłać wiadomości do użytkownika: ' . $user_email,
	    	    ['Content-Type: text/plain; charset=UTF-8']
	    	);


	}



adm_log3("777");



} // <-- function adm_notify_new_customer($user_id)
