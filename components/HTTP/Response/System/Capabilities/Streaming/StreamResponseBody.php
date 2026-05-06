<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Response\System\Capabilities\Streaming;

use Closure;
use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\StreamInterface;

final readonly class StreamResponseBody
{
    /**
     * @param  Closure(): iterable<string>  $chunks
     */
    public function __construct(private Closure $chunks)
    {
    }

    public function toStream(): StreamInterface
    {
        $stream = Utils::streamFor('');

        foreach (($this->chunks)() as $chunk) {
            $stream->write($chunk);
        }

        $stream->rewind();

        return $stream;
    }
}
