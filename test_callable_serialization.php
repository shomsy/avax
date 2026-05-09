<?php

require "/home/shomsy/projects/avax/vendor/autoload.php";
use Avax\Components\Foundation\CallableSerialization\System\PublicSurface\CallableSerialization;

$closure = fn() => "hello from callable serialization";
$payload = CallableSerialization::encode($closure, 'test-key');

echo "Payload: " . substr($payload, 0, 80) . "...\n";

// Decode in same process
$result = CallableSerialization::decode($payload, 'test-key');
if (isset($result['closure'])) {
    echo "Result: " . $result['closure']() . "\n";
} else {
    echo "Failure: " . $result['failure']->message . "\n";
}
