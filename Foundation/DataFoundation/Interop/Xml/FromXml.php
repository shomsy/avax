<?php

declare(strict_types=1);

namespace Avax\DataFoundation\Interop\Xml;

use Avax\DataFoundation\Arrhae;
use Avax\DataFoundation\Exceptions\EncodingException;
use SimpleXMLElement;

/**
 * XML decoding boundary.
 */
final readonly class FromXml
{
    public static function toArrhae(string $xml) : Arrhae
    {
        return new Arrhae(items: self::toArray(xml: $xml));
    }

    /**
     * @return array<mixed>
     */
    public static function toArray(string $xml) : array
    {
        $element = simplexml_load_string($xml, SimpleXMLElement::class);

        if ($element === false) {
            throw EncodingException::xmlDecodingFailed(error: 'Malformed XML payload.');
        }

        return self::convert(element: $element);
    }

    /**
     * @return array<mixed>
     */
    private static function convert(SimpleXMLElement $element) : array
    {
        $values = [];

        foreach ($element->children() as $child) {
            $name          = $child->getName();
            $values[$name] = $child->count() > 0
                ? self::convert(element: $child)
                : (string) $child;
        }

        return $values;
    }
}
