<?php

add_shortcode('adm_registration_form_zainteresowany_oferta', 'custom_wc_registration_form_fixed');

function custom_wc_registration_form_fixed() {
    ob_start();

    if (is_user_logged_in()) {
        echo '<p>Jesteś już zalogowany.</p>';
        return ob_get_clean();
    }

    if (!empty($_POST['custom_registration_form_submitted'])) {
        $username = sanitize_user($_POST['username']);
        $email = sanitize_email($_POST['email']);

        $errors = new WP_Error();

        if (empty($username) || empty($email)) {
            $errors->add('missing_fields', 'Wszystkie pola są wymagane.');
        }

        if (username_exists($username)) {
            $errors->add('username_exists', 'Nazwa użytkownika jest już zajęta.');
        }

        if (email_exists($email)) {
            $errors->add('email_exists', 'Ten adres e-mail jest już zarejestrowany.');
        }

        if (!is_email($email)) {
            $errors->add('invalid_email', 'Nieprawidłowy adres e-mail.');
        }

        if (empty($errors->errors)) {
            $password = wp_generate_password(12, true);

            $user_id = wp_create_user($username, $password, $email);

            if (!is_wp_error($user_id)) {
                wp_update_user([
                    'ID' => $user_id,
                    'role' => 'customer' // nadaj rolę klienta WooCommerce
                ]);

                // Wyślij e-mail do użytkownika


$mail_content = "Witaj %s,\n\n Twoje konto w sklepie %s zostało pomyślnie utworzone.\n\nGdy uporamy się z pracą na pasiece, przygotujemy dla Ciebie ofertę hurtową.\n\n";

if ( function_exists( 'generate_password_reset_link' ) ) {
	$mail_content .= "Możesz już zalogować się w hurtowni.\n Oto link do utworzenia hasła: \n ". generate_password_reset_link($email);
}

$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
$message = sprintf(
    $username,
    $blogname,
    $username,
    $password,
    wp_login_url()
);

$mailer = WC()->mailer();
$wrapped_message = $mailer->wrap_message('Twoje konto zostało utworzone', nl2br($message));

$headers = ['Content-Type: text/html; charset=UTF-8'];

wp_mail($email, 'Twoje konto w sklepie ' . $blogname, $wrapped_message, $headers);



                echo '<p style="color:green;">Sukces! Niebawem wyślemy do Ciebie ofertę hurtową.</p>';
                return ob_get_clean();
            } else {
                echo '<p style="color:red;">Błąd rejestracji: ' . esc_html($user_id->get_error_message()) . '</p>';
                return ob_get_clean();
            }
        } else {
            foreach ($errors->get_error_messages() as $error) {
                echo '<p style="color:red;">' . esc_html($error) . '</p>';
            }
        }
    }

    ?>
    <form method="post" action="">
        <p>
            <label for="username">Nazwa użytkownika</label><br>
            <input type="text" name="username" required>
        </p>
        <p>
            <label for="email">Adres e-mail</label><br>
            <input type="email" name="email" required>
        </p>
        <input type="hidden" name="custom_registration_form_submitted" value="1">
        <p>
            <button type="submit">Zarejestruj</button>
        </p>
    </form>
    <?php

    return ob_get_clean();
}

