
// Wysyłka liczona po liczbie produktow
// 

// 1. Dodaj pole "Koszt dostawy za sztukę w edycji produktu (panel admina)
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
        'description' => 'Ustaw koszt dostawy za jedną sztukę tego produktu. Wysyłka zostanie obliczona jako suma: ilosc koszt.'
    ]);
});

// 2. Zapisz wartość pola po zapisaniu produktu
add_action('woocommerce_process_product_meta', function ($post_id) {
    if (isset($_POST['_delivery_cost_per_item'])) {
        update_post_meta($post_id, '_delivery_cost_per_item', wc_clean($_POST['_delivery_cost_per_item']));
    }
});

// 3. Nadpisz koszt wysyłki dla metody "Kurier" (np. flat_rate:2)
add_filter('woocommerce_package_rates', 'custom_shipping_cost_for_kurier', 20, 2);

function custom_shipping_cost_for_kurier($rates, $package) {
    $target_method_id = 'flat_rate:2'; // <-- Upewnij się, źe to ID Twojej metody "Kurier"

    $custom_shipping_cost = 0;

    foreach ($package['contents'] as $item) {
        $product_id = $item['product_id'];
        $qty = $item['quantity'];
        $cost_per_item = get_post_meta($product_id, '_delivery_cost_per_item', true);

        if ($cost_per_item !== '' && is_numeric($cost_per_item)) {
            $custom_shipping_cost += floatval($cost_per_item) * $qty;
        }
    }

    // Nadpisz koszt tylko dla metody "Kurier"
    foreach ($rates as $rate_id => $rate) {
        if ($rate_id === $target_method_id) {
            $rates[$rate_id]->cost = $custom_shipping_cost;

            // Wyzeruj podatki (jesli nie nie uzywasz stawek VAT do wysylki)
            if (!empty($rates[$rate_id]->taxes) && !is_null($rates[$rate_id]->taxes) ) {
                foreach ($rates[$rate_id]->taxes as $key => $tax) {
                    // $rates[$rate_id]->taxes[$key] = 0;
                }
            }
        }
    }

    return $rates;
}

