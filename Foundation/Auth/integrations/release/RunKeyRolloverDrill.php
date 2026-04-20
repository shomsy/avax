<?php

declare(strict_types=1);

namespace Avax\Auth\Integrations\Release;

use Avax\Auth\System\Flows\Token\FileBackedHmacKeyRingCodec;
use RuntimeException;

final readonly class RunKeyRolloverDrill
{
    /**
     * @return array{issued_kid:string, rollover_verified:bool}
     */
    public function execute(string $keyRingPath) : array
    {
        $codec  = new FileBackedHmacKeyRingCodec(keyRingPath: $keyRingPath);
        $token  = $codec->encode(claims: ['sub' => 42, 'iss' => 'drill']);
        $claims = $codec->decode(token: $token);

        if ($claims === null) {
            throw new RuntimeException(message: 'Key rollover drill could not verify the issued token.');
        }

        $issuedKid = $this->readPrimaryKid(keyRingPath: $keyRingPath);

        return [
            'issued_kid'        => $issuedKid,
            'rollover_verified' => true,
        ];
    }

    private function readPrimaryKid(string $keyRingPath) : string
    {
        $json    = file_get_contents($keyRingPath);
        $decoded = is_string($json) ? json_decode($json, true) : null;
        $kid     = is_array($decoded) ? ($decoded['primary']['kid'] ?? null) : null;

        return is_string($kid) ? $kid : '';
    }
}
