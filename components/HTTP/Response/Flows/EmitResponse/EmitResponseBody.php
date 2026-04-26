<?php

declare(strict_types=1);

namespace components\HTTP\Response\Flows\EmitResponse;

use components\HTTP\Response\Capabilities\Streams\RewindStream;
use Psr\Http\Message\ResponseInterface;

final class EmitResponseBody
{
    public function __invoke(ResponseInterface $response) : void
    {
        $stream = new RewindStream()(stream: $response->getBody());

        while ( ! $stream->eof() ) {
            echo $stream->read(length: 8192);
        }
    }
}
