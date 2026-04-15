<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Flow\Token;

use Avax\Auth\System\Flow\Token\FileBackedHmacKeyRingCodec;
use PHPUnit\Framework\TestCase;

final class FileBackedHmacKeyRingCodecTest extends TestCase
{
    public function testCodecSupportsRolloverWithoutRedeploy() : void
    {
        $path = $this->createKeyRingFile(configuration: [
                                                            'primary'      => [
                                                                'kid'       => '2026-04',
                                                                'secret'    => 'secret-a',
                                                                'algorithm' => 'HS256',
                                                            ],
                                                            'verification' => [],
                                                        ]);

        $codec    = new FileBackedHmacKeyRingCodec(keyRingPath: $path);
        $oldToken = $codec->encode(claims: ['sub' => 1, 'iat' => 1, 'nbf' => 1, 'exp' => 2, 'jti' => 'old-token']);

        file_put_contents(
            $path,
            json_encode([
                            'primary' => [
                                'kid' => '2026-05',
                                                                                                                                            'secret' => 'secret-b',
                                                                                                                                                                                                  'algorithm' => 'HS256',
                            ],
                                                                                                                                                                                                                                                                                                                                                                'verification' => [
                                                                                                                                                                                                                                                                                                                                                                    [
                                                                                                                                                                                                                                                                                                                                                                        'kid' => '2026-04',
                                                                                                                                                                                                                                                            'secret' => 'secret-a',
                                                                                                                                                                                                                                                                                                                                                                        'algorithm' => 'HS256',
                                                                                                                                                                                                                                                                                                                                                                    ],
                                                                                                                                                                                                                                                                                                                                                                ],
                        ], JSON_THROW_ON_ERROR)
        );

        $newToken = $codec->encode(claims: ['sub' => 1, 'iat' => 2, 'nbf' => 2, 'exp' => 3, 'jti' => 'new-token']);

        $this->assertNotNull(actual: $codec->decode(token: $oldToken));
        $this->assertNotNull(actual: $codec->decode(token: $newToken));
    }

    /**
     * @param array<string, mixed> $configuration
     */
    private function createKeyRingFile(array $configuration) : string
    {
        $path = tempnam(sys_get_temp_dir(), 'auth-keyring-');
        self::assertIsString(actual: $path);
        file_put_contents($path, json_encode($configuration, JSON_THROW_ON_ERROR));

        return $path;
    }

    public function testCodecSupportsCryptoAgilityAcrossAlgorithms() : void
    {
        $path = $this->createKeyRingFile(configuration: [
                                                            'primary'      => [
                                                                'kid'       => '2026-04',
                                                                'secret'    => 'secret-a',
                                                                'algorithm' => 'HS256',
                                                            ],
                                                            'verification' => [],
                                                        ]);

        $codec    = new FileBackedHmacKeyRingCodec(keyRingPath: $path);
        $oldToken = $codec->encode(claims: ['sub' => 7, 'iat' => 1, 'nbf' => 1, 'exp' => 2, 'jti' => 'agility-old']);

        file_put_contents(
            $path,
            json_encode([
                            'primary' => [
                                'kid' => '2026-06',
                                                                                                                                            'secret' => 'secret-b',
                                                                                                                                                                                                  'algorithm' => 'HS512',
                            ],
                                                                                                                                                                                                                                                                                                                                                                'verification' => [
                                                                                                                                                                                                                                                                                                                                                                    [
                                                                                                                                                                                                                                                                                                                                                                        'kid' => '2026-04',
                                                                                                                                                                                                                                                            'secret' => 'secret-a',
                                                                                                                                                                                                                                                                                                                                                                        'algorithm' => 'HS256',
                                                                                                                                                                                                                                                                                                                                                                    ],
                                                                                                                                                                                                                                                                                                                                                                ],
                        ], JSON_THROW_ON_ERROR)
        );

        $newToken = $codec->encode(claims: ['sub' => 7, 'iat' => 2, 'nbf' => 2, 'exp' => 3, 'jti' => 'agility-new']);

        $this->assertNotNull(actual: $codec->decode(token: $oldToken));
        $this->assertNotNull(actual: $codec->decode(token: $newToken));
    }
}
