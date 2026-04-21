<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Flows\Token;

use Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\Codec\HmacTokenCodec;
use Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\Codec\MultiKeyHmacTokenCodec;
use PHPUnit\Framework\TestCase;

final class MultiKeyHmacTokenCodecTest extends TestCase
{
    public function testCodecVerifiesTokensAcrossKeyRolloverWindow() : void
    {
        $oldKey = new HmacTokenCodec(secret: 'secret-old', keyId: '2026-03-primary');
        $newKey = new HmacTokenCodec(secret: 'secret-new', keyId: '2026-04-primary');
        $codec  = new MultiKeyHmacTokenCodec(
            primaryCodec      : $newKey,
            verificationCodecs: [$oldKey]
        );

        $claims = [
            'sub' => 1,
            'iat' => 1,
            'nbf' => 1,
            'exp' => 2,
            'jti' => 'token-1',
        ];

        $this->assertSame(expected: $claims, actual: $codec->decode(token: $oldKey->encode(claims: $claims)));
        $this->assertSame(expected: $claims, actual: $codec->decode(token: $codec->encode(claims: $claims)));
    }
}
