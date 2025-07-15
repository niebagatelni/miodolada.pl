<?php

// ---------------------------------------------------
// Dodaj na stronie produktu formularza z trzema polami liczbowymi do wyboru ilości smaku

add_action( 'woocommerce_before_add_to_cart_button', 'dodaj_pola_liczbowe_do_formularza' );
function dodaj_pola_liczbowe_do_formularza() {
    $qty_malina = isset($_POST['qty_malina']) ? intval($_POST['qty_malina']) : 0;
    $qty_mieta = isset($_POST['qty_mieta']) ? intval($_POST['qty_mieta']) : 0;
    $qty_imbir = isset($_POST['qty_imbir']) ? intval($_POST['qty_imbir']) : 0;

    echo '<div id="taste-quantity-fields" class="taste-quantity-fields" style="margin-bottom:15px;">';
    echo '<div class="taste-box" style="display:block;" ><label for="qty_malina" style="margin-bottom:5px;">Malina</label> <input type="number" id="qty_malina" name="qty_malina" min="0" value="'.esc_attr($qty_malina).'" step="1" style="width:80px; margin-right:15px;" /></div>';
    echo '<div class="taste-box" style="display:block;" ><label for="qty_mieta"  style="margin-bottom:5px;">Mięta</label> <input type="number" id="qty_mieta" name="qty_mieta" min="0" value="'.esc_attr($qty_mieta).'" step="1" style="width:80px; margin-right:15px;" /></div>';
    echo '<div class="taste-box" style="display:block;" ><label for="qty_imbir"  style="margin-bottom:5px;">Imbir</label> <input type="number" id="qty_imbir" name="qty_imbir" min="0" value="'.esc_attr($qty_imbir).'" step="1" style="width:80px;" /></div>';
    echo '</div>';

?>

    <style>
.taste-quantity-fields {
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.taste-box {
    display: inline-flex;
    align-items: center;
}
.taste-box label {
    display: inline-block;
    min-width: 80px;
    margin-right: 10px;
    text-align: right;
}
.taste-box input {
    width: 60px;
}
</style>
<?php


 echo '<script name="smaki-miodolady">
    document.addEventListener("DOMContentLoaded", function() {
        var select = document.getElementById("smak");
        var box = document.getElementById("taste-quantity-fields");
        if (!select || !box) return;
        function showHideMixBox() {
            if(select.value && select.value.toLowerCase() === "mix") {
                box.style.display = "";
            } else {
                box.style.display = "none";
            }
        }
        select.addEventListener("change", showHideMixBox);
        showHideMixBox();
    });
    </script>';


}



// ---------------------------------------------------
// Dodaj pole "Maksymalna suma mixa" do edycji produktu w ustawieniach Główne

add_action('woocommerce_product_options_general_product_data', function() {
    global $post;
    echo '<div class="options_group">';
    woocommerce_wp_text_input([
        'id' => '_mix_max_sum',
        'label' => 'Maksymalna suma mixa',
        'desc_tip' => true,
        'description' => 'Maksymalna suma butelek dla wariantu mix (domyślnie 324)',
        'type' => 'number',
        'custom_attributes' => [
            'min' => '1',
            'step' => '1'
        ]
    ]);
    echo '</div>';
});
add_action('woocommerce_process_product_meta', function($post_id) {
    if (isset($_POST['_mix_max_sum'])) {
        update_post_meta($post_id, '_mix_max_sum', intval($_POST['_mix_max_sum']));
    }
});




// ---------------------------------------------------
// Uniwersalna funkcja do sprawdzania czy wybrany wariant to mix
function is_mix_selected($source = null) {
    if ($source === null) {
        return isset($_POST['attribute_smak']) && strtolower($_POST['attribute_smak']) === 'mix';
    }
    if (is_array($source)) {
        if (isset($source['variation']['attribute_smak'])) {
            return strtolower($source['variation']['attribute_smak']) === 'mix';
        }
        if (isset($source['attribute_smak'])) {
            return strtolower($source['attribute_smak']) === 'mix';
        }
    }
    return false;
}





// ---------------------------------------------------
// Walidacja - liczby > 0 oraz suma == max (wartość max wzięta z ustawień produktu)

add_filter( 'woocommerce_add_to_cart_validation', function($passed, $product_id, $quantity) {
    if ( is_mix_selected() && isset($_POST['qty_malina'], $_POST['qty_mieta'], $_POST['qty_imbir']) ) {
        $qty_malina = intval($_POST['qty_malina']);
        $qty_mieta = intval($_POST['qty_mieta']);
        $qty_imbir = intval($_POST['qty_imbir']);
        $suma = $qty_malina + $qty_mieta + $qty_imbir;
        $max = get_post_meta($product_id, '_mix_max_sum', true);
        if (!$max || !is_numeric($max) || $max < 1) {
            wc_add_notice('Nie udało się pobrać maksymalnej sumy mixa dla tego produktu.', 'error');
            adm_log3('Nie udało się pobrać maksymalnej sumy mixa dla tego produktu');
            return false;
        }
        if (!$max || $max < 1) { $max = 324; }
        if ( $qty_malina < 0 || $qty_mieta < 0 || $qty_imbir < 0 ) {
            wc_add_notice( 'Wartości ilości nie mogą być ujemne.', 'error' );
            return false;
        }
        if ( $suma === 0 ) {
            wc_add_notice( 'Musisz podać co najmniej jedną ilość większą od 0.', 'error' );
            return false;
        }
        if ( $suma != $max ) {
            $roznica = $suma - $max;
            $info = $roznica > 0
                ? 'Za dużo: o ' . abs($roznica) . ' butelek.'
                : 'Za mało: o ' . abs($roznica) . ' butelek.';
            wc_add_notice( 'Suma ilości musi wynosić dokładnie ' . $max . '. ' . $info, 'error' );
            return false;
        }
    }
    return $passed;
}, 10, 3 );




// ---------------------------------------------------
// Zapisywanie danych mixa do koszyka


add_filter( 'woocommerce_add_cart_item_data', function($cart_item_data, $product_id, $variation_id ) {
    if ( is_mix_selected() && isset($_POST['qty_malina'], $_POST['qty_mieta'], $_POST['qty_imbir']) ) {
        $cart_item_data['custom_qty'] = array(
            'qty_malina' => intval($_POST['qty_malina']),
            'qty_mieta' => intval($_POST['qty_mieta']),
            'qty_imbir' => intval($_POST['qty_imbir']),
        );
        $cart_item_data['unique_key'] = md5( microtime().rand() );
    }
    return $cart_item_data;
}, 10, 3 );




// ---------------------------------------------------
// Wyświetlanie danych mixa w koszyku pod produktem


add_filter( 'woocommerce_get_item_data', function($item_data, $cart_item) {
    if ( is_mix_selected($cart_item) && isset( $cart_item['custom_qty'] ) ) {
        $item_data[] = array(
            'name' => 'Malina',
            'value' => $cart_item['custom_qty']['qty_malina'],
        );
        $item_data[] = array(
            'name' => 'Mięta',
            'value' => $cart_item['custom_qty']['qty_mieta'],
        );
        $item_data[] = array(
            'name' => 'Imbir',
            'value' => $cart_item['custom_qty']['qty_imbir'],
        );
    }
    return $item_data;
}, 10, 2 );




// ---------------------------------------------------
// Zapisanie danych mixa do meta zamówienia

add_action( 'woocommerce_checkout_create_order_line_item', function($item, $cart_item_key, $values, $order ) {
    if ( is_mix_selected($values) ) {
        $custom_qty = isset($values['custom_qty']) ? $values['custom_qty'] : null;
        if ( is_array($custom_qty) ) {
            if ( isset( $custom_qty['qty_malina'] ) ) {
                $item->add_meta_data( 'qty_malina', $custom_qty['qty_malina'], true );
            }
            if ( isset( $custom_qty['qty_mieta'] ) ) {
                $item->add_meta_data( 'qty_mieta', $custom_qty['qty_mieta'], true );
            }
            if ( isset( $custom_qty['qty_imbir'] ) ) {
                $item->add_meta_data( 'qty_imbir', $custom_qty['qty_imbir'], true );
            }
        } else {
            adm_log3('Brak custom_qty w values: ' . json_encode($values));
        }
    }
}, 10, 4 );





// Domyślnie zaznaczone "Użyj tego samego adresu do rozliczeń płatności" na stronie zamówienia
add_filter('woocommerce_ship_to_different_address_checked', '__return_false');


// Wyświetlanie szczegółów smaków mixa na stronie podsumowania zamówienia (order received)
/*
add_action('woocommerce_order_item_meta_start', function($item_id, $item, $order) {
    $qty_malina = $item->get_meta('qty_malina');
    $qty_mieta  = $item->get_meta('qty_mieta');
    $qty_imbir  = $item->get_meta('qty_imbir');
   if (($qty_malina !== '' && $qty_malina !== null) || ($qty_mieta !== '' && $qty_mieta !== null) || ($qty_imbir !== '' && $qty_imbir !== null)) {
        echo '<div class="mix-flavours-summary" style="font-size:0.95em; margin-top:2px;">';
        echo '<strong>Smaki mixa:</strong> ';
        $flavours = [];
        if ($qty_malina !== '' && $qty_malina !== null) $flavours[] = 'Malina: <b>' . esc_html($qty_malina) . '</b>';
        if ($qty_mieta  !== '' && $qty_mieta !== null) $flavours[] = 'Mięta: <b>' . esc_html($qty_mieta) . '</b>';
        if ($qty_imbir  !== '' && $qty_imbir !== null) $flavours[] = 'Imbir: <b>' . esc_html($qty_imbir) . '</b>';
        echo implode(', ', $flavours);
        echo '</div>';
    }
}, 10, 3);
*/



add_filter('woocommerce_order_item_display_meta_key', function($display_key, $meta, $item) {
    if ($meta->key === 'qty_malina') return 'Malina';
    if ($meta->key === 'qty_mieta')  return 'Mięta';
    if ($meta->key === 'qty_imbir')  return 'Imbir';
    return $display_key;
}, 20, 3);



add_filter('woocommerce_get_price_html', 'pokaz_netto_i_brutto', 10, 2);

function pokaz_netto_i_brutto($price_html, $product) {
    $netto  = wc_get_price_excluding_tax( $product );
    $brutto = wc_get_price_including_tax( $product );
    $waluta = get_woocommerce_currency_symbol();

    $html  = '<span class="price-netto"><span class="price-numbers">' . number_format($netto, 2, ',', ' ') . '</span> ' . $waluta . '</span> (netto)<br>';
    $html .= '<span class="price-brutto"><span class="price-numbers">' . number_format($brutto, 2, ',', ' ') . '</span> ' . $waluta . '</span> (brutto)';

    // Pobierz ilość sztuk z meta `_mix_max_sum`
    $ilosc_sztuk = (int) get_post_meta($product->get_id(), '_mix_max_sum', true);

    if ($ilosc_sztuk > 0) {
        $netto_jednostkowo  = $netto / $ilosc_sztuk;
        $brutto_jednostkowo = $brutto / $ilosc_sztuk;

        $html .= '<br><span class="price-jednostkowa"><span class="price-numbers">' . number_format($netto_jednostkowo, 2, ',', ' ') . '</span> / ';
        $html .= '<span class="price-numbers">' . number_format($brutto_jednostkowo, 2, ',', ' ') . '</span> (' . $waluta . ') za 1 but. </span>';
    }

    return $html;
}

