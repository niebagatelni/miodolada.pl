<?php

add_action('wpforms_process_complete', 'adm_wpforms_email_to_user', 10, 4);

function adm_wpforms_email_to_user($fields, $entry, $form_data, $entry_id) {

adm_log3("email-to-user START");

    if ((int)$form_data['id'] !== 58) {
        return;
    }


$username = sanitize_text_field($fields['fields']['name']);
$email = sanitize_email($fields['fields']['email']);
$username   = esc_html($username);
$blogname   = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);


if( get_user_by('email', $email) ) {
    adm_log2('[wpforms-email-to-user] Nie udało się zarejestrować użytkownika: ' . $email);

    wp_mail(
        get_option('admin_email'),
        'Błąd rejestracji użytkownika',
        'Użytkownik o adresie e-mail ' . $email . ' nie został poprawnie zapisany w bazie.',
        ['Content-Type: text/plain; charset=UTF-8']
    );

    return;
}







$message_html = '
    <p>Witaj <strong>' . $username . '</strong>,</p>
    <p>Twoje konto w hurtowni <a href="https://miodolada.pl"><strong>miodolada.pl</strong></a> zostało pomyślnie utworzone.</p>
    <p>Gdy uporamy się z pracą na pasiece, przygotujemy dla Ciebie ofertę hurtową.</p>
';



if ( function_exists( 'generate_password_reset_link' ) ) {

	adm_log2("[wpforms-email-to-user] Brakuje funkcji generowania linku do resetu hasła");

	$reset_link = generate_password_reset_link($email);
	if ( $reset_link ) {
		adm_log2("[wpforms-email-to-user] Linku do resetu hasła jest niepoprawny");
		$message_html .= '<p>Możesz już zalogować się w hurtowni. Kliknij w poniższy link, aby ustawić swoje hasło:</p>';
		$message_html .= '<p><a href="' . esc_url($reset_link) . '" style="background-color:#96588a;color:#fff;padding:12px 24px;text-decoration:none;border-radius:4px;display:inline-block;">Ustaw hasło</a></p>';
	}
}

$message_html .= '<p>Dziękujemy,<br>Zespół ' . esc_html($blogname) . '</p>';



$mailer = WC()->mailer();
$wrapped_message = $mailer->wrap_message('Twoje konto zostało utworzone', $message_html);
$headers = ['Content-Type: text/html; charset=UTF-8'];
$sent = wp_mail($email, 'Twoje konto w sklepie ' . $blogname, $wrapped_message, $headers);



if (!$sent) {
    adm_log2('[wpforms-email-to-user] Nie udało się wysłać e-maila do ' . $email . ' przy rejestracji konta.');

    wp_mail(
        get_option('admin_email'),
        'Błąd wysyłki e-maila (rejestracja użytkownika)',
        'Nie udało się wysłać wiadomości do użytkownika: ' . $email,
        ['Content-Type: text/plain; charset=UTF-8']
    );
}

} // <-- function adm_wpforms_email_to_user


add_action('wpforms_process_complete', 'adm_wpforms_email_to_user', 10, 4);
