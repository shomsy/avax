<?php

require "/home/shomsy/projects/avax/vendor/autoload.php";
use Laravel\SerializableClosure\SerializableClosure;

$closure = fn() => "hello from file";
$s = SerializableClosure::unsigned($closure);
$serialized = serialize($s);
echo "Serialized: " . strlen($serialized) . PHP_EOL;

$restored = unserialize($serialized);
echo "Restored: " . get_class($restored) . PHP_EOL;
echo "Result: " . $restored() . PHP_EOL;
