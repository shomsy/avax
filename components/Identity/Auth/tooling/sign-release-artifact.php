<?php

declare(strict_types=1);

use Avax\Auth\Integrations\Release\SignReleaseArtifact;

require dirname(path: __DIR__) . '/vendor/autoload.php';

$artifactPath   = $argv[1] ?? null;
$privateKeyPath = $argv[2] ?? null;

if (! is_string(value: $artifactPath) || $artifactPath === '' || ! is_string(value: $privateKeyPath) || $privateKeyPath === '') {
    fwrite(stream: STDERR, data: "Usage: php tooling/sign-release-artifact.php <artifact> <private-key.pem>\n");
    exit(1);
}

$privateKey = file_get_contents(filename: $privateKeyPath);

if ($privateKey === false) {
    fwrite(stream: STDERR, data: "Could not read private key: {$privateKeyPath}\n");
    exit(1);
}

$signer = new SignReleaseArtifact();

echo $signer->execute(artifactPath: $artifactPath, privateKeyPem: $privateKey) . PHP_EOL;
