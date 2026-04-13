<?php

declare(strict_types=1);

use Avax\Auth\Integrations\Release\SignReleaseArtifact;

require dirname(__DIR__) . '/vendor/autoload.php';

$artifactPath = $argv[1] ?? null;
$privateKeyPath = $argv[2] ?? null;

if (! is_string($artifactPath) || $artifactPath === '' || ! is_string($privateKeyPath) || $privateKeyPath === '') {
    fwrite(STDERR, "Usage: php tooling/sign-release-artifact.php <artifact> <private-key.pem>\n");
    exit(1);
}

$privateKey = file_get_contents($privateKeyPath);

if ($privateKey === false) {
    fwrite(STDERR, "Could not read private key: {$privateKeyPath}\n");
    exit(1);
}

$signer = new SignReleaseArtifact();

echo $signer->execute(artifactPath: $artifactPath, privateKeyPem: $privateKey) . PHP_EOL;
