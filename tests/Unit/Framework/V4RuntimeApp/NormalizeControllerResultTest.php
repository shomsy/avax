<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Framework\V4RuntimeApp;

use Avax\Framework\System\Capabilities\ResponseNormalization\NormalizeControllerResult;
use Avax\Components\HTTP\Response\ResponseFactory;
use Psr\Http\Message\ResponseInterface;
use Stringable;
use Avax\Tests\TestCase;

/**
 * @covers \Avax\Framework\System\Capabilities\ResponseNormalization\NormalizeControllerResult
 */
final class NormalizeControllerResultTest extends TestCase
{
    private NormalizeControllerResult $normalizer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->normalizer = new NormalizeControllerResult();
    }

    public function testNormalizeNullReturnsEmptyResponse(): void
    {
        $response = $this->normalizer->normalize(result: null);
        self::assertSame(200, $response->getStatusCode());
    }

    public function testNormalizeStringReturnsTextResponse(): void
    {
        $response = $this->normalizer->normalize(result: 'Hello World');
        self::assertSame(200, $response->getStatusCode());
        $body = (string) $response->getBody();
        self::assertSame('Hello World', $body);
    }

    public function testNormalizeEmptyStringReturnsEmptyResponse(): void
    {
        $response = $this->normalizer->normalize(result: '');
        self::assertSame(200, $response->getStatusCode());
    }

    public function testNormalizeArrayReturnsJsonResponse(): void
    {
        $data = ['name' => 'AvaX', 'version' => 4];
        $response = $this->normalizer->normalize(result: $data);
        self::assertSame(200, $response->getStatusCode());
        $body = (string) $response->getBody();
        $decoded = json_decode($body, true);
        self::assertSame(['name' => 'AvaX', 'version' => 4], $decoded);
        self::assertStringContainsString('application/json', $response->getHeaderLine('Content-Type'));
    }

    public function testNormalizeEmptyArrayReturnsJsonResponse(): void
    {
        $response = $this->normalizer->normalize(result: []);
        $body = (string) $response->getBody();
        self::assertSame('[]', $body);
    }

    public function testNormalizeResponseInterfaceReturnsAsIs(): void
    {
        $factory = new ResponseFactory();
        $original = $factory->html(html: 'Direct response');

        $response = $this->normalizer->normalize(result: $original);

        self::assertSame($original, $response);
        self::assertSame(200, $response->getStatusCode());
    }

    public function testNormalizeJsonSerializableReturnsJson(): void
    {
        $jsonSerializable = new class implements \JsonSerializable {
            /** @return array{json: 'serializable'} */
            public function jsonSerialize(): array
            {
                return ['json' => 'serializable'];
            }
        };

        $response = $this->normalizer->normalize(result: $jsonSerializable);
        $body = (string) $response->getBody();
        self::assertStringContainsString('json', $body);
        self::assertStringContainsString('serializable', $body);
    }

    public function testNormalizeStringableReturnsText(): void
    {
        $stringable = new class implements Stringable {
            public function __toString(): string
            {
                return 'Stringable response';
            }
        };

        $response = $this->normalizer->normalize(result: $stringable);
        $body = (string) $response->getBody();
        self::assertStringContainsString('Stringable response', $body);
    }

    public function testNormalizeIntegerReturnsJson(): void
    {
        $response = $this->normalizer->normalize(result: 42);
        $body = (string) $response->getBody();
        self::assertSame('42', $body);
    }

    public function testNormalizeBooleanReturnsJson(): void
    {
        $response = $this->normalizer->normalize(result: true);
        $body = (string) $response->getBody();
        self::assertSame('true', $body);
    }
}
