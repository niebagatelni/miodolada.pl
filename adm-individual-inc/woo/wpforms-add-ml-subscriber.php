<?php

/*
	$ml_data = array(
		'email'	 =>	$data['email'],	
		'name'	 =>	"",
		'fields' => array(
			"name"		=>	$data['first_name'],
			"last_name"	=>	$data['last_name'],
			"company"	=>	$data['company'],
			"phone"		=>	$data['phone'],
			"nip"		=>	$data['tax_no'],
			"zrodlo"	=>	"miodolada-hurt"
			)
	);
	
*/	



	adm_log3("ML sub START!!");

    if ((int)$form_data['id'] !== 58) {
        return;
    }


	if ( empty($data['email']) || empty($data['name']) ) {
		adm_log2("Brak email lub nazwy w `wpforms-add-ml-subscriber`");
		return;
	}


	$ml_jdata = json_encode($data, JSON_UNESCAPED_UNICODE);

 file_put_contents(ABSPATH . 'ml_jdata.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));


	$request_type = 'POST';
	$CURLOPT_URL = 'https://api.mailerlite.com/api/v2/groups/112989751/subscribers';
	if (!defined('ML_API_KEY')) define('ML_API_KEY', 'dc52e84d9ab80759d811ac3fd3aec497');

	$curl_data = array(
		CURLOPT_URL => $CURLOPT_URL,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => $request_type,		// POST, GET, PUT
		CURLOPT_POSTFIELDS =>$ml_jdata,
		CURLOPT_HTTPHEADER => array(
			'X-MailerLite-ApiKey: '.ML_API_KEY.'',
			'Content-Type: application/json',
			//'Cookie: PHPSESSID=36a405667fac6642505de3a8df48bad8'
		)
	);

$curl = curl_init();
curl_setopt_array($curl, $curl_data);

$response = curl_exec($curl);
$response_error = curl_error($curl);
$http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

curl_close($curl);


if ($http_status !== 200) {
    adm_log3("Problem z cURL Mailerlite `wpforms-add-ml-subscriber.php`. HTTP_STATUS: $http_status");
}
if ($response_error) {
    adm_log3("Błąd cURL Mailerlite `wpforms-add-ml-subscriber.php`: $response_error");
}



	adm_log3("ML sub KONIEC!!");
//*/

