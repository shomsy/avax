<?php

declare(strict_types=1);

namespace Avax\Components\Auth\Integrations\Release;

use RuntimeException;
use SensitiveParameter;

final readonly class SignReleaseArtifact
{
    public function execute(string $artifactPath, #[SensitiveParameter] string $privateKeyPem) : string
    {
        $artifact = file_get_contents(filename: $artifactPath);

        if ($artifact === false) {
            throw new RuntimeException(message: "Could not read artifact: {$artifactPath}");
        }

        $privateKey = openssl_pkey_get_private(private_key: $privateKeyPem);

        if ($privateKey === false) {
            throw new RuntimeException(message: 'Private key could not be loaded for artifact signing.');
        }

        $signature = '';

        if (! openssl_sign(data: $artifact, signature: $signature, private_key: $privateKey, algorithm: OPENSSL_ALGO_SHA256)) {
            throw new RuntimeException(message: 'Artifact signing failed.');
        }

        return base64_encode(string: (string) $signature);
    }
}
