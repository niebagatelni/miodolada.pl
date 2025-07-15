<?php
$ml_log_actions = 1; // Włącz logowanie działań MailerLite
$fname_log = "[.]";

// Konfiguracja: czy hasło jest wymagane przy rejestracji?
//global $adm_password_required;
$adm_password_required = false; // Ustaw true jeśli chcesz wymagać hasła


 
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
        add_role('klient_hurtowy', 'Klient hurtowy', $customer_capabilities);
    }
}
add_action('init', 'utworz_role_zainteresowany_oferta', 20);




if ( !function_exists( 'generate_password_reset_link' ) ) {
function generate_password_reset_link($email) {
    if ( ! email_exists( $email ) ) { return false; }

    $user = get_user_by( 'email', $email );
    if ( ! $user || is_wp_error( $user ) ) { return false; }

    $reset_key = get_password_reset_key( $user );
    if ( is_wp_error( $reset_key ) ) { return false; }

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
		// adm_include_in_theme('/adm-individual-inc/woo/registration-custom-fields-adminpanel.php');
		// adm_include_in_theme('/adm-individual-inc/woo/registration-custom-fields-account-dashboard.php');
		// adm_include_in_theme('/adm-individual-inc/woo/registration-custom-email-notifications.php');
		// adm_include_in_theme('/adm-individual-inc/woo/registration-custom-fields-form-shortcode.php');

        // adm_include_in_theme('/adm-individual-inc/woo/wpforms-add-user.php');
		// adm_include_in_theme('/adm-incindividual-/woo/wpforms-email-to-user.php.php');

    adm_include_in_theme('/adm-individual-inc/woo/customer-registration-custom-fields.php');
    adm_include_in_theme('/adm-individual-inc/woo/customer-registration-notification.php');
	adm_include_in_theme('/adm-individual-inc/woo/mailerlite-add-subscriber.php');
	adm_include_in_theme('/adm-individual-inc/woo/customer-registration-role-link-handler.php');
	adm_include_in_theme('/adm-individual-inc/woo/basket-checkout-disable.php');
	adm_include_in_theme('/adm-individual-inc/woo/shipping-cost-by-product-count.php');
	adm_include_in_theme('/adm-individual-inc/woo/user_meta.php');
	adm_include_in_theme('/adm-individual-inc/woo/miodolada-pallet.php');
	adm_include_in_theme('/adm-individual-inc/woo/checkout-changes.php');
    


}





add_action( 'after_setup_theme', function() {
    remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
});



// Przycisk "Kupuję i płacę"

add_action('wp_head', function() {
    if (is_checkout()) {
        ?>
        <style>
        .wc-block-components-checkout-place-order-button__text {
            font-size: 0 !important;
        }
        
        .wc-block-components-checkout-place-order-button__text::before {
            content: "Kupuję";
            font-size: 16px;
            font-weight: inherit;
        }
        </style>
        <?php
    }
});
add_action('wp_footer', function() {
    if (is_checkout() || is_cart()) {
        ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                let observer;
                let intervalId;

                function changeButtonText() {
                    let stopWatchingFlag = false;

                    const button = document.querySelector('.wc-block-components-checkout-place-order-button__text');
                    if (button && button.textContent.trim() !== ('Kupuję') ) {
                        button.textContent = 'Kupuję';
                        stopWatchingFlag = true;
                    }

                    const button2 = document.querySelector('.wc-proceed-to-checkout .checkout-button.wc-forward');
                    if (button2) {
                        console.log(button2.textContent.trim());
                        if (button2.textContent.trim() !== 'Dalej') {
                            button2.textContent = 'Dalej';
                            stopWatchingFlag = true;
                        }
                    }

                    if (stopWatchingFlag) {
                        stopWatching();
                    }
                }

                function stopWatching() {
                    if (observer) {
                        observer.disconnect();
                        observer = null;
                    }
                    if (intervalId) {
                        clearInterval(intervalId);
                        intervalId = null;
                    }
                }

                function startObserving() {
                    if (document.body) {
                        if ('MutationObserver' in window) {
                            observer = new MutationObserver(changeButtonText);
                            observer.observe(document.body, { childList: true, subtree: true });
                        } else {
                            intervalId = setInterval(changeButtonText, 300);
                        }
                        changeButtonText();
                    } else {
                        setTimeout(startObserving, 200);
                    }
                }

                startObserving();
            });
        </script>
        <?php
    }
});




// Dopisz `(netto)`  w koszyku 

add_filter('woocommerce_cart_item_price', 'dopisz_netto_do_ceny_produktu', 10, 3);
function dopisz_netto_do_ceny_produktu($price_html, $cart_item, $cart_item_key) {
    if (strpos($price_html, 'netto') === false) {
        $price_html .= ' <small>netto</small>';
    }
    return $price_html;
}

add_filter('woocommerce_cart_item_subtotal', 'dopisz_netto_do_sumy_produktu', 10, 3);
function dopisz_netto_do_sumy_produktu($subtotal_html, $cart_item, $cart_item_key) {
    if (strpos($subtotal_html, 'netto') === false) {
        $subtotal_html .= ' <small>netto</small>';
    }
    return $subtotal_html;
}

add_filter('woocommerce_cart_totals_order_total_html', 'dopisz_netto_do_sumy_koszyka', 10, 1);
function dopisz_netto_do_sumy_koszyka($value) {
    if (strpos($value, 'netto') === false) {
        $value .= ' <small>brutto</small>';
    }
    return $value;
}

add_filter('woocommerce_cart_subtotal', function($subtotal, $compound, $cart) {
    // Dopisanie ' netto' po wartości subtotal
    if (strpos($subtotal, 'netto') === false) {
        $subtotal .= ' <small>netto</small>';
    }
    return $subtotal;
}, 10, 3);








