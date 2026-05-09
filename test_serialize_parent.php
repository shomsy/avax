<?php

require "/home/shomsy/projects/avax/vendor/autoload.php";
use Laravel\SerializableClosure\SerializableClosure;

$captured = "captured_value";
$closure = fn() => "captured: " . $captured;
$s = SerializableClosure::unsigned($closure);
$serialized = serialize($s);

$dir = "/home/shomsy/projects/avax/.tmp-test";
if (!is_dir($dir)) mkdir($dir);
file_put_contents("{$dir}/closure_serialized.txt", $serialized);
echo "Written to {$dir}/closure_serialized.txt\n";
