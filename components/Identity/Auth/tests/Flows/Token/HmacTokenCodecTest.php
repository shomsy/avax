<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\Tests\Flows\Token;

use Avax\Components\Identity\Auth\System\Capabilities\Identity\Tokens\Runtime\Codec\HmacTokenCodec;
use Avax\Tests\TestCase;
use PHPUnit\Framework\TestCase;

final class HmacTokenCodecTest extends TestCase
{
    public function testCodecRejectsTokenSignedWithDifferentKeyVersion() : void
    {
        $encoder         = new HmacTokenCodec(
            secret: 'secret',
            keyId : '2026-04-primary'
        );
        $otherKeyVersion = new HmacTokenCodec(
            secret: 'secret',
            keyId : '2026-05-primary'
        );

        $token = $encoder->encode(claims: [
                                              'sub' => 1,
                                              'iat' => 1,
                                              'nbf' => 1,
                                              'exp' => 2,
                                              'jti' => 'token-1',
                                          ]);

        $this->assertNull(actual: $otherKeyVersion->decode(token: $token));
        $this->assertNotNull(actual: $encoder->decode(token: $token));
    }

    public function testCodecRejectsTamperedSignature() : void
    {
        $codec = new HmacTokenCodec(secret: 'secret');

        $token = $codec->encode(claims: [
                                            'sub' => 1,
                                            'iat' => 1,
                                            'nbf' => 1,
                                            'exp' => 2,
                                            'jti' => 'token-1',
                                        ]);

        [$header, $claims, $signature] = explode(separator: '.', string: $token);
        $tamperedSignature = substr(string: $signature, offset: 0, length: -1) . (substr(string: $signature, offset: -1) === 'A' ? 'B' : 'A');
        $tamperedToken     = "{$header}.{$claims}.{$tamperedSignature}";

        $this->assertNull(actual: $codec->decode(token: $tamperedToken));
        $this->assertNotNull(actual: $codec->decode(token: $token));
    }
}
