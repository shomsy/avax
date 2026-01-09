<?php

declare(strict_types=1);

namespace Avax\DataHandling\ArrayHandling\Traits;

use Exception;
use InvalidArgumentException;
use LogicException;
use SimpleXMLElement;

/**
 * Trait ArrayConversionTrait
 *
 * Provides methods to convert the collection to various formats such as JSON, XML, and arrays.
 * Also includes methods to filter the collection by including or excluding specific keys.
 *
 * This trait is intended to be used within classes that manage collections of data,
 * such as arrays of associative arrays or objects. It leverages the `AbstractDependenciesTrait`
 * for dependency management, ensuring that the underlying data collection is properly handled.
 *
 * @package Avax\DataHandling\ArrayHandling\Traits
 */
trait ArrayConversionTrait
{
    use AbstractDependenciesTrait;

    /**
     * Convert the collection to a JSON string.
     *
     * This method serializes the collection into a JSON-formatted string. It accepts optional
     * JSON encoding options to customize the output.
     *
     * @param int $options Optional JSON encoding options. Default is 0.
     *
     * @return string JSON-encoded string representation of the collection.
     *
     * @throws InvalidArgumentException If the collection contains data that cannot be encoded to JSON.
     *
     * ```
     * $arrh = new Arrhae(['apple', 'banana', 'cherry']);
     * $json = $arrh->toJson(); // Returns '["apple","banana","cherry"]'
     *
     * // With JSON_PRETTY_PRINT option
     * $jsonPretty = $arrh->toJson(JSON_PRETTY_PRINT);
     * /*
     * Returns:
     * [
     *     "apple",
     *     "banana",
     *     "cherry"
     * ]
     *
     */
    public function toJson(int $options = 0) : string
    {
        $json = json_encode(value: $this->toArray(), flags: $options);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException(
                message: 'Failed to encode collection to JSON: ' . json_last_error_msg()
            );
        }

        return $json;
    }

    /**
     * Convert the collection and nested collections to an array.
     *
     * This method recursively converts each item in the collection to an array if it implements
     * the `toArray` method. Otherwise, it retains the item's original value.
     *
     * @return array Array representation of the collection.
     *
     * @throws LogicException If the collection contains non-escapable items.
     *
     * ```
     * $arrh = new Arrhae([
     *     ['id' => 1, 'score' => 80],
     *     ['id' => 2, 'score' => 90],
     *     ['id' => 3, 'score' => 70],
     * ]);
     * $array = $arrh->toArray();
     * // Returns [
     * //     ['id' => 1, 'score' => 80],
     * //     ['id' => 2, 'score' => 90],
     * //     ['id' => 3, 'score' => 70],
     * // ]
     * ```
     */
    public function toArray() : array
    {
        return array_map(callback: static function ($item) {
            if (is_object(value: $item) && method_exists(object_or_class: $item, method: 'toArray')) {
                return $item->toArray();
            }

            return $item;
        },               array   : $this->getItems());
    }

    /**
     * Convert the collection to an XML string with a customizable root element.
     *
     * This method serializes the collection into an XML-formatted string. It allows specifying
     * a custom root element name. All values are escaped to ensure valid XML.
     *
     * @param string $rootElement The root element name for the XML. Default is 'root'.
     *
     * @return string XML representation of the collection.
     *
     * @throws Exception If the XML conversion fails.
     *
     * ```
     * $arrh = new Arrhae(['apple', 'banana', 'cherry']);
     * $xml = $arrh->toXml('fruits');
     * /*
     * Returns:
     * <?xml version="1.0"?>
     * <fruits>
     *     <item>apple</item>
     *     <item>banana</item>
     *     <item>cherry</item>
     * </fruits>
     *
     */
    public function toXml(string $rootElement = 'root') : string
    {
        try {
            $xml = new SimpleXMLElement(data: sprintf('<%s/>', $rootElement));
            $this->arrayToXml(data: $this->toArray(), xml: $xml);

            return $xml->asXML();
        } catch (Exception $exception) {
            throw new Exception(
                message : 'Failed to convert collection to XML: ' . $exception->getMessage(),
                code    : $exception->getCode(),
                previous: $exception
            );
        }
    }

    /**
     * Helper method to recursively convert an array to XML.
     *
     * @param array            $data The data to convert.
     * @param SimpleXMLElement $xml  The XML element to append data to.
     */
    private function arrayToXml(array $data, SimpleXMLElement &$xml) : void
    {
        foreach ($data as $key => $value) {
            // Handle numeric keys by using 'item' as the tag name
            if (is_numeric(value: $key)) {
                $key = 'item';
            }

            if (is_array(value: $value)) {
                $child = $xml->addChild(qualifiedName: $key);
                $this->arrayToXml(data: $value, xml: $child);
            } else {
                $xml->addChild(qualifiedName: $key, value: htmlspecialchars(string: (string) $value));
            }
        }
    }

    /**
     * Include only specified keys in the collection.
     *
     * This method filters the collection to retain only the specified keys. It returns a new
     * instance of the collection with the filtered items.
     *
     * @param array $keys Keys to retain in the collection.
     *
     * @return static Collection instance with specified keys.
     *
     * @throws InvalidArgumentException If the keys array is empty.
     *
     * ```
     * $arrh = new Arrhae(['name' => 'Alice', 'age' => 25, 'city' => 'Wonderland']);
     * $filtered = $arrh->only(['name', 'city']);
     * // Returns ['name' => 'Alice', 'city' => 'Wonderland']
     * ```
     */
    public function only(array $keys) : static
    {
        if ($keys === []) {
            throw new InvalidArgumentException(message: 'Keys array cannot be empty.');
        }

        $filteredItems = array_filter(
            array   : $this->getItems(),
            callback: static fn($item, $key) : bool => in_array(needle: $key, haystack: $keys, strict: true),
            mode    : ARRAY_FILTER_USE_BOTH
        );

        return new static(items: $filteredItems);
    }

    /**
     * Exclude specified keys from the collection.
     *
     * This method filters the collection to remove the specified keys. It returns a new
     * instance of the collection without the excluded items.
     *
     * @param array $keys Keys to exclude from the collection.
     *
     * @return static Collection instance without specified keys.
     *
     * @throws InvalidArgumentException If the keys array is empty.
     *
     * ```
     * $arrh = new Arrhae(['name' => 'Alice', 'age' => 25, 'city' => 'Wonderland']);
     * $filtered = $arrh->except(['age']);
     * // Returns ['name' => 'Alice', 'city' => 'Wonderland']
     * ```
     */
    public function except(array $keys) : static
    {
        if ($keys === []) {
            throw new InvalidArgumentException(message: 'Keys array cannot be empty.');
        }

        $filteredItems = array_filter(
            array   : $this->getItems(),
            callback: static fn($item, $key) : bool => ! in_array(needle: $key, haystack: $keys, strict: true),
            mode    : ARRAY_FILTER_USE_BOTH
        );

        return new static(items: $filteredItems);
    }
}
