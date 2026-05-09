<?php

require "/home/shomsy/projects/avax/vendor/autoload.php";
use Laravel\SerializableClosure\SerializableClosure;

$dir = "/home/shomsy/projects/avax/.tmp-test";
$serialized = file_get_contents("{$dir}/closure_serialized.txt");
echo "Read " . strlen($serialized) . " bytes\n";

$restored = unserialize($serialized);
echo "Result: " . $restored() . PHP_EOL;
echo "PID: " . getmypid() . PHP_EOL;
