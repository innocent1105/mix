<?php
require_once('vendor/autoload.php'); 

use GuzzleHttp\Client;

$API_KEY = 'd17d75a7437f62d4ce3e80b31a0e575eb34745288f699b7ebe9dee98d22305e5';
$ACCOUNT_ID = '0e9fb03a-637d-4e61-beeb-253ae5c9b366'; // 36-character account UUID
$PHONE = '0960883940'; // test recipient phone
$OPERATOR = 'mtn'; // airtel, mtn, zamtel (Zambia)
$COUNTRY = 'zm'; // zm = Zambia, mw = Malawi
$AMOUNT = 5; // amount to send (minor units)
$REFERENCE = 'test_' . time(); // unique reference


$client = new Client();

try {
    $response = $client->request('POST', 'https://api.lenco.co/access/v2/transfers/mobile-money', [
        'headers' => [
            'Authorization' => 'Bearer ' . $API_KEY,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ],
        'json' => [
            'accountId' => $ACCOUNT_ID,
            'amount' => $AMOUNT,
            'reference' => $REFERENCE,
            'phone' => $PHONE,
            'operator' => $OPERATOR,
            'country' => $COUNTRY,
            'narration' => 'Test transfer'
        ],
    ]);

    $body = $response->getBody()->getContents();
    $data = json_decode($body, true);

    echo "<h2>Transfer Response</h2>";
    echo "<pre>";
    print_r($data);
    echo "</pre>";

} catch (\Exception $e) {
    echo "<h2>Error</h2>";
    echo "<pre>";
    echo $e->getMessage();
    echo "</pre>";
}
