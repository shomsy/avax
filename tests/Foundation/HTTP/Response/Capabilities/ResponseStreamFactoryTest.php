<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\HTTP\Response\Capabilities;

use Avax\Components\HTTP\Response\Capabilities\Streams\ResponseStreamFactory;
use PHPUnit\Framework\TestCase;

final class ResponseStreamFactoryTest extends TestCase
{
    public function test_factory_creates_streams_from_string_resource_and_file() : void
    {
        $factory = new ResponseStreamFactory();

        $stringStream = $factory->createStreamFromString(content: 'alpha');
        $resource     = fopen(filename: 'php://temp', mode: 'r+');
        fwrite(stream: $resource, data: 'beta');
        rewind(stream: $resource);
        $resourceStream = $factory->createStreamFromResource(resource: $resource);

        $path = tempnam(directory: sys_get_temp_dir(), prefix: 'response-stream-');
        self::assertNotFalse($path);
        file_put_contents(filename: $path, data: 'gamma');

        try {
            $fileStream = $factory->openFileStream(path: $path);

            self::assertSame('alpha', (string) $stringStream);
            self::assertSame('beta', (string) $resourceStream);
            self::assertSame('gamma', (string) $fileStream);
        } finally {
            @unlink(filename: $path);
        }
    }
}
