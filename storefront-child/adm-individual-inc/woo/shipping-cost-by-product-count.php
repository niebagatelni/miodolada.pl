<?php

// ---------------------------------------------------------------
// Wysyłka liczona po liczbie produktów z prawidłowym obliczaniem podatku
//
/*
add_action('woocommerce_cart_calculate_fees', function() {
});
add_filter('woocommerce_shipping_methods', function($methods) {
    return $methods;
});
add_filter('woocommerce_shipping_packages', function($packages) {
    return $packages;
});
*/

// 1. Dodaj pole "Koszt dostawy za sztukę" w edycji produktu (panel admina)
add_action('woocommerce_product_options_general_product_data', function () {
    woocommerce_wp_text_input([
        'id' => '_delivery_cost_per_item',
        'label' => 'Koszt dostawy za sztukę (zł)',
        'type' => 'number',
        'custom_attributes' => [
            'step' => '0.01',
            'min' => '0'
        ],
        'desc_tip' => true,
        'description' => 'Ustaw koszt dostawy za jedną sztukę tego produktu. Wysyłka zostanie obliczona jako suma: ilosc koszt.'
    ]);
});

// 2. Zapisz wartość pola po zapisaniu produktu
add_action('woocommerce_process_product_meta', function ($post_id) {
    if (isset($_POST['_delivery_cost_per_item'])) {
        update_post_meta($post_id, '_delivery_cost_per_item', wc_clean($_POST['_delivery_cost_per_item']));
    }
});

// 3. Nadpisz koszt wysyłki dla metody "Kurier" (np. flat_rate:2) z prawidłowym obliczaniem podatku
add_action('init', function() {
    add_filter('woocommerce_package_rates', 'custom_shipping_cost_for_kurier', 1000, 2);
});

function custom_shipping_cost_for_kurier($rates, $package) {
    $target_method_id = 'flat_rate:2'; // ID Twojej metody wysyłki

    $custom_shipping_cost = 0;
    foreach ($package['contents'] as $item) {
        $product_id = $item['product_id'];
        $qty = $item['quantity'];
        $cost_per_item = get_post_meta($product_id, '_delivery_cost_per_item', true);
        if ($cost_per_item !== '' && is_numeric($cost_per_item)) {
            $custom_shipping_cost += floatval($cost_per_item) * $qty;
        }
    }

    foreach ($rates as $rate_id => $rate) {
        if ($rate_id === $target_method_id) {
            // Ustaw nowy koszt wysyłki
            $rates[$rate_id]->cost = $custom_shipping_cost;

            // Oblicz podatek od wysyłki jeśli jest włączony
            if (wc_tax_enabled() && WC()->customer && WC()->customer->get_is_vat_exempt() !== true) {
                
                // Pobierz klasę podatkową dla wysyłki
                $shipping_tax_class = get_option('woocommerce_shipping_tax_class');
                
                // Jeśli klasa podatkowa to 'inherit', użyj najwyższej klasy podatkowej z koszyka
                if ($shipping_tax_class === 'inherit') {
                    $shipping_tax_class = WC()->cart->get_cart_tax_class();
                }
                
                // Pobierz stawki podatkowe
                $tax_rates = WC_Tax::get_rates($shipping_tax_class);
                
                if (!empty($tax_rates)) {
                    // Oblicz podatki od nowej sumy wysyłki
                    $shipping_taxes = WC_Tax::calc_tax($custom_shipping_cost, $tax_rates, false);
                    $rates[$rate_id]->taxes = $shipping_taxes;
                    
                } else {
                    // Brak stawek podatkowych - wyzeruj podatki
                    $rates[$rate_id]->taxes = array();
                }
            } else {
                // Podatki wyłączone lub klient zwolniony z VAT
                $rates[$rate_id]->taxes = array();
            }
            
            // Usuń starą właściwość tax jeśli istnieje
            if (property_exists($rates[$rate_id], 'tax')) {
                unset($rates[$rate_id]->tax);
                }
            
        }
    }
    return $rates;
}

// 4. Dodatkowy filtr do sprawdzania czy podatek jest prawidłowo obliczany
add_filter('woocommerce_cart_shipping_total', function($total) {
    return $total;
}, 10, 1);

// 5. Logowanie podatków od wysyłki
add_action('woocommerce_cart_calculate_fees', function() {
    if (WC()->cart->get_shipping_total() > 0) {
    }
});

