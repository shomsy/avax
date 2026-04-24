<?php

declare(strict_types=1);

namespace Avax\HTTP\Response;

use Avax\HTTP\Response\Flows\EmitResponse\EmitResponse;
use Psr\Http\Message\ResponseInterface;

/**
 * Public side-effect owner for writing a response to the PHP runtime.
 */
final class ResponseEmitter
{
    public function __invoke(ResponseInterface $response) : void
    {
        $this->emit(response: $response);
    }

    public function emit(ResponseInterface $response) : void
    {
        (new EmitResponse())($response);
    }
}
