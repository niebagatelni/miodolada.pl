<?php
// Dodaje przycisk "Anuluj zamówienie" oraz "Przywróć zamówienie" do kolumny działań na liście zamówień klienta WooCommerce
add_filter('woocommerce_my_account_my_orders_actions', function($actions, $order) {
    // Przycisk anulowania dla zamówień oczekujących na płatność lub wstrzymanych
    if (in_array($order->get_status(), ['pending', 'on-hold'])) {
        $actions['cancel'] = [
            'url' => add_query_arg([
                'confirm_cancel' => $order->get_id(),
                '_wpnonce' => wp_create_nonce('confirm_cancel_' . $order->get_id())
            ], wc_get_endpoint_url('orders', '', wc_get_page_permalink('myaccount'))),
            'name' => __('Anuluj', 'storefront-child'),
            'button' => true,
        ];
    }

    // Przycisk przywracania tylko dla zamówień anulowanych
    if ($order->get_status() === 'cancelled') {
        $actions['restore'] = [
            'url' => add_query_arg([
                'confirm_restore' => $order->get_id(),
                '_wpnonce' => wp_create_nonce('confirm_restore_' . $order->get_id())
            ], wc_get_endpoint_url('orders', '', wc_get_page_permalink('myaccount'))),
            'name' => __('Przywróć', 'storefront-child'),
            'button' => true,
        ];
    }

    return $actions;
}, 20, 2);

// Dodaj style CSS dla przycisków zamówień
add_action('wp_head', function() {
    if (is_account_page()) {
        ?>
        <style>
        .woocommerce-orders-table__cell.woocommerce-orders-table__cell-order-actions {
            display: grid;
            justify-content: start;
            align-items: center;
        }

        .woocommerce-button .cancel{
            background-color: #dc3545 !important;
            color           : white !important;
        }

        .woocommerce-button .restore{
            background-color: #007cba !important;
            color           : white !important;
        }


        </style>
        <?php
    }
});

// Wyświetla stronę potwierdzenia
add_action('woocommerce_account_orders_endpoint', function() {
    // Potwierdzenie anulowania
    if (isset($_GET['confirm_cancel']) && wp_verify_nonce($_GET['_wpnonce'] ?? '', 'confirm_cancel_' . $_GET['confirm_cancel'])) {
        $order_id = absint($_GET['confirm_cancel']);
        $order = wc_get_order($order_id);
        
        if ($order && $order->get_user_id() === get_current_user_id() && in_array($order->get_status(), ['pending', 'on-hold'])) {
            echo '<div class="adm-info">';
            echo '<h3>Potwierdzenie anulowania zamówienia</h3>';
            echo '<p>Czy na pewno chcesz anulować zamówienie #' . $order->get_order_number() . '?</p>';
            echo '<form method="post" style="display: inline-block; margin-right: 10px;">';
            echo '<input type="hidden" name="cancel_order_confirmed" value="' . $order_id . '">';
            echo wp_nonce_field('cancel_order_' . $order_id, '_wpnonce', true, false);
            echo '<input type="submit" class="button" value="Tak, anuluj zamówienie" style="background: #dc3545; color: white;">';
            echo '</form>';
            echo '<a href="' . wc_get_endpoint_url('orders', '', wc_get_page_permalink('myaccount')) . '" class="button">Nie, wróć do zamówień</a>';
            echo '</div>';
            return;
        }
    }
    
    // Potwierdzenie przywracania
    if (isset($_GET['confirm_restore']) && wp_verify_nonce($_GET['_wpnonce'] ?? '', 'confirm_restore_' . $_GET['confirm_restore'])) {
        $order_id = absint($_GET['confirm_restore']);
        $order = wc_get_order($order_id);
        
        if ($order && $order->get_user_id() === get_current_user_id() && $order->get_status() === 'cancelled') {
            echo '<div class="adm-info">';
            echo '<h3>Potwierdzenie przywrócenia zamówienia</h3>';
            echo '<p>Czy na pewno chcesz przywrócić zamówienie #' . $order->get_order_number() . '?</p>';
            echo '<form method="post" style="display: inline-block; margin-right: 10px;">';
            echo '<input type="hidden" name="restore_order_confirmed" value="' . $order_id . '">';
            echo wp_nonce_field('restore_order_' . $order_id, '_wpnonce', true, false);
            echo '<input type="submit" class="button" value="Tak, przywróć zamówienie" style="background: #28a745; color: white;">';
            echo '</form>';
            echo '<a href="' . wc_get_endpoint_url('orders', '', wc_get_page_permalink('myaccount')) . '" class="button">Nie, wróć do zamówień</a>';
            echo '</div>';
            return;
        }
    }
}, 5);

// Obsługa akcji anulowania i przywracania zamówienia
add_action('template_redirect', function() {
    if (is_account_page()) {
        // Anulowanie potwierdzone
        if (isset($_POST['cancel_order_confirmed'])) {
            $order_id = absint($_POST['cancel_order_confirmed']);
            $order = wc_get_order($order_id);
            
            if ($order && $order->get_user_id() === get_current_user_id() && in_array($order->get_status(), ['pending', 'on-hold'])) {
                if (wp_verify_nonce($_POST['_wpnonce'] ?? '', 'cancel_order_' . $order_id)) {
                    $order->update_status('cancelled', __('Zamówienie anulowane przez klienta.', 'storefront-child'));
                    wc_add_notice(__('Zamówienie zostało anulowane przez Ciebie.', 'storefront-child'), 'success');
                    wp_safe_redirect(wc_get_endpoint_url('orders', '', wc_get_page_permalink('myaccount')));
                    exit;
                }
            }
        }

        // Przywracanie potwierdzone
        if (isset($_POST['restore_order_confirmed'])) {
            $order_id = absint($_POST['restore_order_confirmed']);
            $order = wc_get_order($order_id);
            
            if ($order && $order->get_user_id() === get_current_user_id() && $order->get_status() === 'cancelled') {
                if (wp_verify_nonce($_POST['_wpnonce'] ?? '', 'restore_order_' . $order_id)) {
                    $order->update_status('on-hold', __('Zamówienie przywrócone przez klienta.', 'storefront-child'));
                    wc_add_notice(__('Zamówienie zostało przywrócone.', 'storefront-child'), 'success');
                    wp_safe_redirect(wc_get_endpoint_url('orders', '', wc_get_page_permalink('myaccount')));
                    exit;
                }
            }
        }
    }
});