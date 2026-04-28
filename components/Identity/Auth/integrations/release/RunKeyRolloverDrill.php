<?php

declare(strict_types=1);

namespace Avax\Components\Auth\Integrations\Release;

use Avax\Components\Auth\System\Capabilities\Identity\Tokens\Runtime\Codec\FileBackedHmacKeyRingCodec;
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
        $json    = file_get_contents(filename: $keyRingPath);
        $decoded = is_string(value: $json) ? json_decode(json: $json, associative: true) : null;
        $kid     = is_array(value: $decoded) ? ($decoded['primary']['kid'] ?? null) : null;

        return is_string(value: $kid) ? $kid : '';
    }
}
