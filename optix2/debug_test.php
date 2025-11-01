<?php
// Simple test to see if the classes work in isolation
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/constants.php';

echo "Testing Session...\n";
$session = \App\Helpers\Session::getInstance();
echo "Session created\n";

echo "Testing Security...\n";
$security = new \App\Helpers\Security();
echo "Security created\n";

echo "Generating CSRF token...\n";
$token = $security->generateCsrfToken();
echo "Token: $token\n";

echo "All tests passed!\n";
