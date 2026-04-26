<?php

declare(strict_types=1);

namespace components\HTTP\Response\Capabilities\Body;

use components\HTTP\Response\Capabilities\Streams\ResponseStreamFactory;
use Psr\Http\Message\StreamInterface;

final class ResponseBody
{
    private StreamInterface $stream;

    public function __construct(StreamInterface|null $stream = null)
    {
        $this->stream = $stream ?? new ResponseStreamFactory()->createEmptyStream();
    }

    public function stream() : StreamInterface
    {
        return $this->stream;
    }
}
