<?php
define('FLASK_URL', 'https://your-render-url.onrender.com');

function flask_call($method, $endpoint, $api_key = null, $body = []) {
    if (empty($api_key)) {
        return [
            'status' => 500,
            'data'   => ['error' => 'Missing brooder API key']
        ];
    }

    $ch = curl_init(FLASK_URL . $endpoint);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $api_key
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

function mock_live_reading($brooder_id = 0, $target_temp = null) {
    $base_temp = $target_temp ? floatval($target_temp) : 32.5;
    $offset = intval($brooder_id) * 17;

    return [
        'temperature' => round($base_temp + sin((time() + $offset) / 90) * 0.8, 1),
        'humidity'    => round(58 + sin((time() + $offset) / 120) * 4, 1),
        'recorded_at' => date('Y-m-d H:i:s'),
        'source'      => 'mock'
    ];
}
