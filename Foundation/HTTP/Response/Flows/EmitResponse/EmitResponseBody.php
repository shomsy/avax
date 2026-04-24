<?php

declare(strict_types=1);

namespace Avax\HTTP\Response\Flows\EmitResponse;

use Avax\HTTP\Response\Capabilities\Streams\RewindStream;
use Psr\Http\Message\ResponseInterface;

final class EmitResponseBody
{
    public function __invoke(ResponseInterface $response) : void
    {
        $stream = (new RewindStream())($response->getBody());

        while ( ! $stream->eof() ) {
            echo $stream->read(length: 8192);
        }
    }
}
