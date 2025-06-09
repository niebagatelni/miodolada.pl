<?php
$fname_log = "[.]";
$ml_log_actions = 1; // Włącz logowanie działań MailerLite



function utworz_role_zainteresowany_oferta() {
    if (!get_role('zainteresowany_oferta')) {
        $customer = get_role('customer');
        
        if ($customer) {
            $customer_capabilities = $customer->capabilities;

        } else {
            $customer_capabilities = array(
                'read' => true,
                'view_admin_dashboard' => false,
                'edit_posts' => false,
                'read_posts' => true
            );
        }
        add_role('zainteresowany_oferta', 'Zainteresowany ofertą', $customer_capabilities);

    }
}
add_action('init', 'utworz_role_zainteresowany_oferta', 20);




if ( !function_exists( 'generate_password_reset_link' ) ) {
function generate_password_reset_link($email) {
    if ( ! email_exists( $email ) ) {
        return false;
    }

    $user = get_user_by( 'email', $email );

    if ( ! $user || is_wp_error( $user ) ) {
        return false;
    }

    $reset_key = get_password_reset_key( $user );

    if ( is_wp_error( $reset_key ) ) {
        return false;
    }

    // Budujemy pełny link resetu hasła
       $reset_url = add_query_arg(
            array(
                'key' => $reset_key,
                'id'  => $user->ID,
            ),
            wc_get_endpoint_url( 'lost-password', '', wc_get_page_permalink( 'myaccount' ) )
        );

    	return $reset_url;
}}



function allow_low_password_woocommerce() {
    if (is_account_page() || is_checkout()) {
        wp_dequeue_script('wc-password-strength-meter');
    }
}
add_action('wp_print_scripts', 'allow_low_password_woocommerce', 100);



if( class_exists('WooCommerce') ){
	adm_include_in_theme('/adm-individual-inc/woo/registration-custom-fields-woocommerce.php');
		// adm_include_in_theme('/adm-individual-inc/woo/registration-custom-fields-adminpanel.php');
		// adm_include_in_theme('/adm-individual-inc/woo/registration-custom-fields-account-dashboard.php');
		// adm_include_in_theme('/adm-individual-inc/woo/registration-custom-email-notifications.php');
		// adm_include_in_theme('/adm-individual-inc/woo/registration-custom-fields-form-shortcode.php');

        // adm_include_in_theme('/adm-individual-inc/woo/wpforms-add-user.php');
		// adm_include_in_theme('/adm-incindividual-/woo/wpforms-email-to-user.php.php');

        adm_include_in_theme('/adm-individual-inc/woo/customer-registration-notification.php');
//	adm_include_in_theme('/adm-individual-inc/woo/customer-roles.php');
	adm_include_in_theme('/adm-individual-inc/woo/mailerlite-add-subscriber.php');
	adm_include_in_theme('/adm-individual-inc/woo/customer-registration-role-link-handler.php');
	adm_include_in_theme('/adm-individual-inc/woo/basket-checkout-disable.php');
	adm_include_in_theme('/adm-individual-inc/woo/shipping-cost-by-product-count.php');

}






/*

Priorytety CSS:

color-set		: 10
header-layout		: 22
ustawienia bloga	: 23
custom-blocks		: 25
color-settings-output 	: 100

*/





//*/



