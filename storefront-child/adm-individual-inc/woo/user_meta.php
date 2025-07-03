<?php

// Automatyczna aktualizacja meta WooCommerce i synchronizacja z MailerLite po zrealizowanym zamówieniu
add_action('woocommerce_order_status_completed', function($order_id) {
    $order = wc_get_order($order_id);
    if (!$order) return;

    $user_id = $order->get_user_id();
    if (!$user_id) return;

    // Pobierz aktualne dane WooCommerce
    $order_count = function_exists('wc_get_customer_order_count') ? wc_get_customer_order_count($user_id) : '';
    $total_spent = function_exists('wc_get_customer_total_spent') ? wc_get_customer_total_spent($user_id) : '';

    // Ostatnie zamówienie
    $last_order = '';
    $last_order_id = '';
    $orders = wc_get_orders([
        'customer_id' => $user_id,
        'limit' => 1,
        'orderby' => 'date',
        'order' => 'DESC',
        'status' => array('completed', 'processing', 'on-hold')
    ]);
    if (!empty($orders)) {
        $last_order = $orders[0]->get_date_created() ? $orders[0]->get_date_created()->date('Y-m-d H:i:s') : '';
        $last_order_id = $orders[0]->get_id();
    }

    // Zapisz do user_meta
    update_user_meta($user_id, 'woo_orders_count', $order_count);
    update_user_meta($user_id, 'woo_total_spent', $total_spent);
    update_user_meta($user_id, 'woo_last_order', $last_order);
    update_user_meta($user_id, 'woo_last_order_id', $last_order_id);

    // Wywołaj synchronizację z MailerLite (jeśli funkcja istnieje)
    if (function_exists('ml_add_or_update_subscriber')) {
        ml_add_or_update_subscriber($user_id);
    }
});
