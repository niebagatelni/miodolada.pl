<?php



if( class_exists('WooCommerce') ){
	adm_include_in_theme('/adm-inc/woo/role-zainteresowany.php');
	adm_include_in_theme('/adm-inc/woo/registration-custom-fields-woocommerce-form.php');
	adm_include_in_theme('/adm-inc/woo/registration-custom-fields-adminpanel.php');
	adm_include_in_theme('/adm-inc/woo/registration-custom-fields-account-dashboard.php');
// adm_include_in_theme('/adm-inc/woo/registration-custom-email-notifications.php');
// adm_include_in_theme('/adm-inc/woo/registration-custom-fields-form-shortcode.php');
	adm_include_in_theme('/adm-inc/woo/wpforms-add-user.php');
	// adm_include_in_theme('/adm-inc/woo/wpforms-email-to-user.php.php');
	 adm_include_in_theme('/adm-inc/woo/customer-registration-notification.php');
	 adm_include_in_theme('/adm-inc/woo/basket-checkout-disable.php');

}



/*

Priorytety CSS:

color-set		: 10
header-layout		: 22
ustawienia bloga	: 23
custom-blocks		: 25
color-settings-output 	: 100

*/




function allow_low_password_woocommerce() {
    if (is_account_page() || is_checkout()) {
        wp_dequeue_script('wc-password-strength-meter');
    }
}
add_action('wp_print_scripts', 'allow_low_password_woocommerce', 100);



//*/



