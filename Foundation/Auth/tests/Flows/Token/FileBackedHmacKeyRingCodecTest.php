<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Flows\Token;

use Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\FileBackedHmacKeyRingCodec;
use JsonException;
use PHPUnit\Framework\TestCase;

final class FileBackedHmacKeyRingCodecTest extends TestCase
{
    /**
     * @throws JsonException
     */
    public function testCodecSupportsRolloverWithoutRedeploy() : void
    {
        $path = $this->createKeyRingFile();

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
     *
     * @return string
     * @throws JsonException
     */
    private function createKeyRingFile() : string
    {
        $configuration = [
            'primary'      => [
                'kid'       => '2026-04',
                'secret'    => 'secret-a',
                'algorithm' => 'HS256',
            ],
            'verification' => [],
        ];
        $path          = tempnam(sys_get_temp_dir(), 'auth-keyring-');
        self::assertIsString(actual: $path);
        file_put_contents($path, json_encode($configuration, JSON_THROW_ON_ERROR));

        return $path;
    }

    /**
     * @throws JsonException
     */
    public function testCodecSupportsCryptoAgilityAcrossAlgorithms() : void
    {
        $path = $this->createKeyRingFile();

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
