<?php

declare(strict_types=1);

namespace Avax\Auth\Integrations\Release;

use Avax\Auth\System\Flow\Token\FileBackedHmacKeyRingCodec;
use RuntimeException;

final readonly class RunKeyCompromiseDrill
{
    /**
     * @return array{revoked_old_kid:bool}
     */
    public function execute(string $preRotationKeyRingPath, string $postCompromiseKeyRingPath) : array
    {
        $before = new FileBackedHmacKeyRingCodec($preRotationKeyRingPath);
        $token = $before->encode(['sub' => 7, 'iss' => 'drill']);

        $after = new FileBackedHmacKeyRingCodec($postCompromiseKeyRingPath);

        if ($after->decode($token) !== null) {
            throw new RuntimeException('Compromise drill failed because a retired key still verifies old tokens.');
        }

        return [
            'revoked_old_kid' => true,
        ];
    }
}
