<?php
require_once __DIR__ . '/vendor/autoload.php';

$SECRET_KEY = '';
$ACCOUNT_ID = ''; // source Lenco account
$WALLET_NUMBER = '7984685'; // Lenco wallet to receive funds
$REFERENCE = 'test_lenco_' . time();

$client = new \GuzzleHttp\Client();

try {
    $response = $client->request('POST', 'https://api.lenco.co/access/v2/transfers/lenco-money', [
        'headers' => [
            'Authorization' => 'Bearer ' . $SECRET_KEY,
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/json',
        ],
        'json' => [
            'accountId' => $ACCOUNT_ID,
            'amount'    => 1,            // amount in minor units
            'reference' => $REFERENCE,
            'walletNumber' => $WALLET_NUMBER,
            'narration' => 'Test Lenco Wallet Transfer'
        ]
    ]);

    $data = json_decode($response->getBody(), true);

    echo "<pre>Transfer Response:\n";
    print_r($data);
    echo "</pre>";

} catch (\GuzzleHttp\Exception\ClientException $e) {
    echo "<pre>Error: " . $e->getMessage() . "\n";
    echo $e->getResponse()->getBody();
    echo "</pre>";
}
