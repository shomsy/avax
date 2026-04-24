<?php

declare(strict_types=1);

namespace Avax\DataFoundation\Internal\Conversion;

use Avax\DataFoundation\Exceptions\EncodingException;
use Exception;
use SimpleXMLElement;

/**
 * Encodes simple array-backed values into XML.
 */
final readonly class XmlEncoding
{
    public static function encode(array $value, string $rootElement = 'root') : string
    {
        try {
            $xml = new SimpleXMLElement("<{$rootElement}/>");
            self::append(data: $value, xml: $xml);

            return $xml->asXML() ?: '';
        } catch (Exception $exception) {
            throw EncodingException::xmlEncodingFailed(error: $exception->getMessage());
        }
    }

    private static function append(array $data, SimpleXMLElement $xml) : void
    {
        foreach ($data as $key => $item) {
            $nodeName = is_string($key) ? $key : 'item';

            if (is_array($item)) {
                $child = $xml->addChild($nodeName);
                self::append(data: $item, xml: $child);
                continue;
            }

            $xml->addChild($nodeName, htmlspecialchars((string) $item));
        }
    }
}
