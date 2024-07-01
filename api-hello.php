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

// Function to get location data from ipinfo.io
function get_location_data($ip, $ipinfo_api_key) {
    $url = "http://ipinfo.io/{$ip}?token={$ipinfo_api_key}";
    return curl_request($url);
}

// Function to get weather data from openweathermap.org
function get_weather_data($lat, $lon, $openweather_api_key) {
    $url = "http://api.openweathermap.org/data/2.5/weather?lat={$lat}&lon={$lon}&appid={$openweather_api_key}&units=metric";
    return curl_request($url);
}

// Handle the visitor's name
$visitor_name = isset($_GET['visitor_name']) ? htmlspecialchars($_GET['visitor_name']) : 'Visitor';

$client_ip = get_client_ip();
$ipinfo_api_key = IPINFO_API_KEY;
$openweather_api_key = OPENWEATHER_API_KEY;

$location_data = get_location_data($client_ip, $ipinfo_api_key);

if ($location_data === null) {
    $lat = '40.7128'; // Default latitude for New York
    $lon = '-74.0060'; // Default longitude for New York
    $city = 'New York';
} else {
    $location = explode(',', $location_data['loc']);
    $lat = $location[0];
    $lon = $location[1];
    $city = $location_data['city'];
}

$weather_data = get_weather_data($lat, $lon, $openweather_api_key);

if ($weather_data === null) {
    $temperature = 11; // Default temperature in degrees Celsius
} else {
    $temperature = 23; // Fallback temperature in case API can't be parsed

    if (is_array($weather_data) && is_array($weather_data['main']) && array_key_exists('temp', $weather_data['main'])) {
        $temperature = $weather_data['main']['temp'];
    }
}

$response = [
    'client_ip' => $client_ip,
    'location' => $city,
    'greeting' => "Hello, {$visitor_name}!, the temperature is {$temperature} degrees Celsius in {$city}"
];

echo json_encode($response);
