<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Flow\Token;

use Avax\Auth\System\Flow\Token\HmacTokenCodec;
use PHPUnit\Framework\TestCase;

final class HmacTokenCodecTest extends TestCase
{
    public function testCodecRejectsTokenSignedWithDifferentKeyVersion() : void
    {
        $encoder = new HmacTokenCodec(
            secret: 'secret',
            keyId : '2026-04-primary'
        );
        $otherKeyVersion = new HmacTokenCodec(
            secret: 'secret',
            keyId : '2026-05-primary'
        );

        $token = $encoder->encode([
            'sub' => 1,
            'iat' => 1,
            'nbf' => 1,
            'exp' => 2,
            'jti' => 'token-1',
        ]);

        $this->assertNull($otherKeyVersion->decode($token));
        $this->assertNotNull($encoder->decode($token));
    }
}
