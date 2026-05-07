<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\ContentNegotiation\System\Capabilities\Negotiator;

final readonly class AcceptHeaderParser
{
    /**
     * @return list<string>
     */
    public static function parse(string $header) : array
    {
        if ($header === '') {
            return ['application/json'];
        }

        $parts      = explode(',', $header);
        $priorities = [];

        foreach ($parts as $part) {
            $part = trim($part);

            if ($part === '') {
                continue;
            }

            $segments = explode(';', $part);
            $mime     = trim($segments[0]);
            $quality  = 1.0;

            foreach ($segments as $segment) {
                $segment = trim($segment);
                if (str_starts_with($segment, 'q=')) {
                    $quality = (float) substr($segment, 2);
                    break;
                }
            }

            if ($mime !== '') {
                $priorities[$mime] = $quality;
            }
        }

        arsort($priorities);

        return array_keys($priorities) ?: ['application/json'];
    }

    public static function quality(string $header, string $mime) : float
    {
        $parts = explode(',', $header);

        foreach ($parts as $part) {
            $part       = trim($part);
            $segments   = explode(';', $part);
            $parsedMime = trim($segments[0]);

            if ($parsedMime === $mime || $parsedMime === '*/*') {
                foreach ($segments as $segment) {
                    $segment = trim($segment);

                    if (str_starts_with($segment, 'q=')) {
                        return (float) substr($segment, 2);
                    }
                }

                return 1.0;
            }
        }

        return 0.0;
    }
}
