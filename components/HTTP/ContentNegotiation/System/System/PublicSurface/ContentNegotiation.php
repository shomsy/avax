<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\ContentNegotiation\System\System\PublicSurface;

use Avax\Components\HTTP\ContentNegotiation\System\System\Capabilities\Negotiator\AcceptHeaderParser;
use Psr\Http\Message\RequestInterface;

final readonly class ContentNegotiation
{
    public static function negotiate(
        RequestInterface $request,
        array            $supported = ['application/json', 'application/xml', 'text/csv'],
    ) : NegotiatedContent
    {
        $header = $request->getHeaderLine('Accept');

        $parsed = AcceptHeaderParser::parse($header);

        foreach ($parsed as $mime) {
            if (in_array($mime, $supported, true)) {
                return new NegotiatedContent(
                    mimeType        : $mime,
                    negotiatedFormat: self::formatFor($mime),
                );
            }
        }

        return new NegotiatedContent(
            mimeType        : $supported[0],
            negotiatedFormat: self::formatFor($supported[0]),
        );
    }

    public static function parse(string $acceptHeader) : array
    {
        return AcceptHeaderParser::parse($acceptHeader);
    }

    public static function formatFor(string $mime) : ContentFormatter
    {
        return match ($mime) {
            'application/json', 'application/json-api' => new JsonFormatter(),
            'application/xml', 'text/xml'              => new XmlFormatter(),
            'text/csv'                                 => new CsvFormatter(),
            default                                    => new JsonFormatter(),
        };
    }
}
