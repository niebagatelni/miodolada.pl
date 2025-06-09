<?php

/**
 *  tworzy rolę "Zainteresowany ofertą" z uprawnieniami z roli "customer" (Klient)
 */

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
