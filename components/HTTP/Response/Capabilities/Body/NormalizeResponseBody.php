<?php

declare(strict_types=1);

namespace Avax\HTTP\Response\Capabilities\Body;

use Avax\HTTP\Response\Capabilities\Streams\ResponseStreamFactory;
use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use Stringable;

final class NormalizeResponseBody
{
    public function __construct(
        private readonly ResponseStreamFactory $streams = new ResponseStreamFactory(),
    ) {}

    public function __invoke(mixed $body) : ResponseBody
    {
        if ($body instanceof ResponseBody) {
            return $body;
        }

        if ($body instanceof StreamInterface) {
            return new ResponseBody(stream: $body);
        }

        if (is_resource(value: $body)) {
            return new ResponseBody(stream: $this->streams->createStreamFromResource(resource: $body));
        }

        if ($body === null) {
            return new ResponseBody(stream: $this->streams->createEmptyStream());
        }

        if (is_scalar(value: $body) || $body instanceof Stringable) {
            return new ResponseBody(stream: $this->streams->createStreamFromString(content: (string) $body));
        }

        throw new InvalidArgumentException(message: 'Response body must be null, scalar, stringable, resource, or StreamInterface.');
    }
}
