<?php
require_once __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client;

$SECRET_KEY = '';  // replace with your sandbox key
$REFERENCE = 'collect_3' . time();          // unique reference for this collection
$AMOUNT = 5;                            // amount in minor units
$PHONE = '0961678259';                     // customer's mobile number
$OPERATOR = 'mtn';                      // airtel, mtn, tnm
$COUNTRY = 'zm';                           // zm = Zambia, mw = Malawi
$BEARER = 'merchant';                      // who bears the fee: merchant or customer

$client = new Client();

try {
    $response = $client->request('POST', 'https://api.lenco.co/access/v2/collections/mobile-money', [
        'headers' => [
            'Authorization' => 'Bearer ' . $SECRET_KEY,
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/json',
        ],
        'json' => [
            'amount'   => $AMOUNT,
            'reference'=> $REFERENCE,
            'phone'    => $PHONE,
            'operator' => $OPERATOR,
            'country'  => $COUNTRY,
            'bearer'   => $BEARER,
        ]
    ]);

    $data = json_decode($response->getBody(), true);

    echo "<pre>Collection Response:\n";
    print_r($data);
    echo "</pre>";

} catch (\GuzzleHttp\Exception\ClientException $e) {
    echo "<pre>Error: " . $e->getMessage() . "\n";
    echo $e->getResponse()->getBody();
    echo "</pre>";
}
