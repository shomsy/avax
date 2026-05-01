<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\ContentNegotiation\System\PublicSurface;

use Avax\Components\HTTP\ContentNegotiation\System\Capabilities\Negotiator\AcceptHeaderParser;
use Psr\Http\Message\RequestInterface;
use SimpleXMLElement;

interface ContentFormatter
{
    public function format(mixed $data) : string;

    public function mimeType() : string;
}

final readonly class ContentNegotiation
{
    public static function negotiate(
        RequestInterface $request,
        array $supported = ['application/json', 'application/xml', 'text/csv'],
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
            'application/json', 'application/json-api' => new JsonFormatter,
            'application/xml', 'text/xml'              => new XmlFormatter,
            'text/csv'                                 => new CsvFormatter,
            default                                    => new JsonFormatter,
        };
    }
}

final readonly class NegotiatedContent
{
    public function __construct(
        public string $mimeType,
        public ContentFormatter $formatter,
    ) {}
}

final class JsonFormatter implements ContentFormatter
{
    public function format(mixed $data) : string
    {
        return json_encode($data, JSON_THROW_ON_ERROR | JSON_HEX_TAG | JSON_HEX_APOS);
    }

    public function mimeType() : string
    {
        return 'application/json';
    }
}

final class XmlFormatter implements ContentFormatter
{
    public function format(mixed $data) : string
    {
        if (is_array($data)) {
            return $this->arrayToXml($data);
        }

        return (string) $data;
    }

    private function arrayToXml(array $data) : string
    {
        $xml = new SimpleXMLElement('<root/>');

        $this->arrayToXmlRecursive($data, $xml);

        return $xml->asXML();
    }

    private function arrayToXmlRecursive(array $data, SimpleXMLElement $xml) : void
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $child = $xml->addChild(is_numeric($key) ? 'item' : $key);
                $this->arrayToXmlRecursive($value, $child);
            } else {
                $xml->addChild(is_numeric($key) ? 'item' : $key, (string) $value);
            }
        }
    }

    public function mimeType() : string
    {
        return 'application/xml';
    }
}

final class CsvFormatter implements ContentFormatter
{
    public function format(mixed $data) : string
    {
        if (! is_array($data)) {
            return (string) $data;
        }

        $output = fopen('php://temp', 'r+');

        if (isset($data[0]) && is_array($data[0])) {
            fputcsv($output, array_keys($data[0]));

            foreach ($data as $row) {
                fputcsv($output, $row);
            }
        } else {
            fputcsv($output, array_keys($data));
            fputcsv($output, array_values($data));
        }

        rewind($output);
        $content = stream_get_contents($output);
        fclose($output);

        return $content;
    }

    public function mimeType() : string
    {
        return 'text/csv';
    }
}
