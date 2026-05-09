<?php

require "/home/shomsy/projects/avax/vendor/autoload.php";
use Avax\Components\Foundation\CallableSerialization\System\PublicSurface\CallableSerialization;

$closure = fn() => "hello from worker";
$payload = CallableSerialization::encode($closure, 'worker-key');
$encoded = base64_encode($payload);

// Execute bin/avax
$process = new Symfony\Component\Process\Process(['php', 'bin/avax', '--payload', $encoded]);
$process->setTimeout(10);
$process->run();

echo "Exit: " . $process->getExitCode() . "\n";
echo "Stdout: " . $process->getOutput() . "\n";
echo "Stderr: " . $process->getErrorOutput() . "\n";
