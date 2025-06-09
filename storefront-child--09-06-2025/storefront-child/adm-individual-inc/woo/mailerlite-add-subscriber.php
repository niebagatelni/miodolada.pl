// Funkcja pomocnicza do wywołań API MailerLite
function ml_call_api($url, $type = 'POST', $data = null) {
    if (!defined('ML_API_KEY')) define('ML_API_KEY', 'dc52e84d9ab80759d811ac3fd3aec497');
    $curl_data = array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => $type,
        CURLOPT_HTTPHEADER => array(
            'X-MailerLite-ApiKey: ' . ML_API_KEY,
            'Content-Type: application/json',
        )
    );
    if ($data !== null) {
        $curl_data[CURLOPT_POSTFIELDS] = $data;
    }
    $curl = curl_init();
    curl_setopt_array($curl, $curl_data);
    $response = curl_exec($curl);
    $response_error = curl_error($curl);
    $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    if ($http_status === 200 && !$response_error) {
        if (function_exists('adm_log3')) adm_log3('Sukces: ' . $response);
    } else {
        if ($http_status !== 200 && function_exists('adm_log3')) {
            adm_log3('Problem z cURL Mailerlite. HTTP_STATUS: ' . $http_status);
        }
        if ($response_error && function_exists('adm_log3')) {
            adm_log3('Błąd cURL Mailerlite: '. $response_error);
        }
    }
}
<?php

add_action('user_register', 'ml_add_or_update_subscriber');
add_action('profile_update', 'ml_add_or_update_subscriber', 10, 2);

function ml_add_or_update_subscriber($user_id, $old_user_data = null) {
    $user = get_userdata($user_id);
    if (!$user) {
        return;
    }

    if ( !in_array('customer', (array)$user->roles, true) && !in_array('zainteresowany_oferta', (array)$user->roles, true)) {
        return;
    }

    $zainteresowany_group   = "112989751";
    $customer_group         = '112998441'; 


    // Dane do wysyłki
    $data = [
        'email' => $user->user_email ?? '',
        'fields' => [
            'name' => $user->first_name ?? '',
            'last_name' => $user->last_name ?? '',
            'company' => $user->billing_company ?? '',
            'country' => $user->billing_country ?? '',
            'city' => $user->billing_city ?? '',
            'phone' => $user->billing_phone ?? '',
            'state' => $user->billing_state ?? '',
            'zip' => $user->billing_postcode ?? '',
            'nip' => $user->billing_vat ?? $user->billing_nip ?? '',
            'zrodlo' => 'miodolada-hurt',
            'miodolada_hurt_role' => isset($user->roles) ? implode(',', $user->roles) : '',
            'woo_id' => $user->ID ?? '',
            'woo_orders_count' => $user->woo_orders_count ?? '',
            'woo_total_spent' => $user->woo_total_spent ?? '',
            'woo_last_order' => $user->woo_last_order ?? '',
            'woo_last_order_id' => $user->woo_last_order_id ?? '',
            'woo_registered_date' => $user->user_registered ?? '',
            'woo_last_login_date' => $user->data_ost_wizyty_w_sklepie ?? '',
        ]
    ];
    $roles = $user->roles ?? array();


    if (empty($data['email'])) return;



    $ml_jdata = json_encode($data, JSON_UNESCAPED_UNICODE);

    // Logowanie do pliku adm/ml-action-logs/ml-sub-mm-dd.log
    if (isset($ml_log_actions) && $ml_log_actions == 1) {
        $log_dir = ABSPATH . 'adm/ml-action-logs/';
        if (!is_dir($log_dir)) @mkdir($log_dir, 0777, true);
        $log_file = $log_dir . 'ml-sub-' . date('m-d') . '.log';
        file_put_contents($log_file, date('Y-m-d H:i:s') . ' ' . json_encode($data, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);
    }

    // Dodaj do odpowiedniej grupy
    if (in_array('customer', $roles)) {
        $url = 'https://api.mailerlite.com/api/v2/groups/' . $customer_group . '/subscribers';
        ml_call_api($url, 'POST', $ml_jdata);
        // Usuń z grupy zainteresowany_oferta
        $url_del = 'https://api.mailerlite.com/api/v2/groups/' . $zainteresowany_group . '/subscribers/' . $data['email'];
        ml_call_api($url_del, 'DELETE');
    } elseif (in_array('zainteresowany_oferta', $roles)) {
        $url = 'https://api.mailerlite.com/api/v2/groups/' . $zainteresowany_group . '/subscribers';
        ml_call_api($url, 'POST', $ml_jdata);
    }



} // <-- function ml_add_or_update_subscriber
