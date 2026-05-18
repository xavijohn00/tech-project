<?php
define('FLASK_URL', 'https://your-render-url.onrender.com');
define('FLASK_KEY', 'brooder-a-api-key-changeme');

function flask_call($method, $endpoint, $body = []) {
    $ch = curl_init(FLASK_URL . $endpoint);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . FLASK_KEY
    ]);

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [
        'status' => $httpCode,
        'data'   => json_decode($response, true)
    ];
}
