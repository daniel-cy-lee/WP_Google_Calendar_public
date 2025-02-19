<?php
require_once 'google-api/vendor/autoload.php';

use Google\Client;

$client = new Client();
$client->setAuthConfig(__DIR__ . '/service-account.json');
$client->setScopes(['https://www.googleapis.com/auth/calendar']);

try {
    $token = $client->fetchAccessTokenWithAssertion();
    print_r($token);
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
?>
