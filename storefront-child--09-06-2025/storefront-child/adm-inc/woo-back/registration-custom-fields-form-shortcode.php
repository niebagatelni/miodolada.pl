<?php
/**
 * Shortcode: [rejestracja_firmowa]
 * Formularz rejestracji firmowej zgodny z WooCommerce, z dodatkowymi polami
 */

function formularz_rejestracji_firmowej_shortcode() {
    // Jeżeli użytkownik jest zalogowany, nie pokazuj formularza
    if (is_user_logged_in()) {
        return '<p>' . __('Jesteś już zalogowany.', 'storefront-child') . '</p>';
    }

    ob_start();

    // Wyświetlenie błędów, jeśli są
    wc_print_notices();


    ?>
    <form method="post" class="woocommerce-form woocommerce-form-register register" action="<?php echo esc_url(wc_get_page_permalink('myaccount')); ?>">

        <?php do_action('woocommerce_register_form_start'); ?>

        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="reg_email"><?php esc_html_e('Adres e-mail', 'storefront-child'); ?> <span class="required">*</span></label>
            <input type="email" name="email" id="reg_email" autocomplete="email" value="<?php echo (!empty($_POST['email'])) ? esc_attr(wp_unslash($_POST['email'])) : ''; ?>" required />
        </p>

        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="reg_password"><?php esc_html_e('Hasło', 'storefront-child'); ?> <span class="required">*</span></label>
            <input type="password" name="password" id="reg_password" autocomplete="new-password" required />
        </p>

        <?php
        // Twoje niestandardowe pola
        //dodaj_pola_rejestracji_woocommerce();
        ?>

        <?php do_action('woocommerce_register_form'); ?>

        <p class="woocommerce-form-row form-row">
            <?php wp_nonce_field('woocommerce-register', 'woocommerce-register-nonce'); ?>
            <button type="submit" class="woocommerce-Button button" name="register" value="<?php esc_attr_e('Zarejestruj się', 'storefront-child'); ?>"><?php esc_html_e('Zarejestruj się', 'storefront-child'); ?></button>
        </p>

        <?php do_action('woocommerce_register_form_end'); ?>
    </form>
    <?php

    return ob_get_clean();
}
add_shortcode('rejestracja_firmowa', 'formularz_rejestracji_firmowej_shortcode');
