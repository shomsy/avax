<?php

declare(strict_types=1);

namespace Gemini\HTTP\Response\Helper;

use SimpleXMLElement;

/**
 * XmlFormatter
 *
 * A utility class designed to convert PHP arrays into XML format strings.
 * This can be particularly useful for creating XML responses in an HTTP context.
 */
class XmlFormatter
{
    /**
     * Converts an array to XML format, returning the XML content as a string.
     *
     * @param array  $data     The data to be converted to XML.
     * @param string $rootNode Root node for the XML (default is 'response').
     *
     * @return string XML string representation of the array.
     * @throws \Exception
     */
    public static function format(array $data, string $rootNode = 'response') : string
    {
        // Initialize a new SimpleXMLElement with the specified root node
        $xml = new SimpleXMLElement(data: sprintf('<%s/>', $rootNode));

        // Recursively converts the array into XML elements
        self::arrayToXml(data: $data, xml: $xml);

        // Return XML string, use empty string if conversion fails asXML returns false on failure
        return $xml->asXML() ?: '';
    }

    /**
     * Recursively adds data to the XML object.
     *
     * @param array            $data Data to convert to XML.
     * @param SimpleXMLElement $xml  XML object to append data to.
     */
    private static function arrayToXml(array $data, SimpleXMLElement $xml) : void
    {
        foreach ($data as $key => $value) {
            // Handle numeric keys to ensure valid XML element names
            $key = is_numeric($key) ? 'item' . $key : htmlspecialchars($key);

            if (is_array($value)) {
                // Recurse if value is an array, creating a new child element
                $child = $xml->addChild(qualifiedName: $key);
                self::arrayToXml(data: $value, xml: $child);
            } else {
                // Add simple text node, escaping any HTML entities
                $xml->addChild(qualifiedName: $key, value: htmlspecialchars((string) $value));
            }
        }
    }
}
