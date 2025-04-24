<?php
require __DIR__ . '/../vendor/autoload.php';

use YourVendor\YourSdk\Client;

$client = new Client();
echo "🎉 Your SDK is working! Client class: " . get_class($client) . "\n";
