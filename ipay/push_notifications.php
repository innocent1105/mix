<?php

$payload = [
  'to' => "ExponentPushToken[zVhoMLElqrwDGl51TR0W2i]",
  'sound' => 'default',
  'title' => 'Test Notification',
  'body' => 'Push notifications are working',
];

$ch = curl_init('https://exp.host/--/api/v2/push/send');
curl_setopt_array($ch, [
  CURLOPT_POST => true,
  CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_POSTFIELDS => json_encode($payload),
]);

$response = curl_exec($ch);
curl_close($ch);

echo $response;



