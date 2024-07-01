<?php

require_once '../hng11-config.php';

header('Content-Type: application/json');

// Function to get client's IP address
function get_client_ip() {
    $ip_address = '';
    if (isset($_SERVER['HTTP_CLIENT_IP'])) {
        $ip_address = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip_address = $_SERVER['REMOTE_ADDR'];
    }
    return $ip_address;
}

// Function to make a cURL request
function curl_request($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response === FALSE) {
        return null;
    }
    return json_decode($response, true);
}

// Handle the visitor's name
$visitor_name = isset($_GET['visitor_name']) ? htmlspecialchars($_GET['visitor_name']) : 'Visitor';

$client_ip = get_client_ip();
$weatherapi_key = WEATHERAPI_KEY;

$weather_data = curl_request("http://api.weatherapi.com/v1/current.json?key={$weatherapi_key}&q={$client_ip}");

if ($weather_data === null) {
    $city = 'New York';
    $temperature = 11; // Default temperature in degrees Celsius
} else {
    $city = $weather_data['location']['region'];
    $temperature = $weather_data['current']['temp_c'];
}

$response = [
    'client_ip' => $client_ip,
    'location' => $city,
    'greeting' => "Hello, {$visitor_name}!, the temperature is {$temperature} degrees Celsius in {$city}"
];

echo json_encode($response);
