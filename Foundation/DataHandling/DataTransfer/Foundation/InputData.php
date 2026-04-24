<?php

declare(strict_types=1);

namespace Avax\DataHandling\DataTransfer\Foundation;

use Avax\DataHandling\DataTransfer\DataTransferException;
use JsonSerializable;
use Traversable;

final readonly class InputData
{
    private function __construct(private array $values) {}

    public static function from(array|object $input) : self
    {
        if (is_array(value: $input)) {
            return new self(values: $input);
        }

        if ($input instanceof JsonSerializable) {
            $serialized = $input->jsonSerialize();

            if (! is_array(value: $serialized)) {
                throw new DataTransferException(message: 'JsonSerializable input must serialize to an array.');
            }

            return new self(values: $serialized);
        }

        if ($input instanceof Traversable) {
            return new self(values: iterator_to_array(iterator: $input));
        }

        if (method_exists(object_or_class: $input, method: 'toArray')) {
            $array = $input->toArray();

            if (! is_array(value: $array)) {
                throw new DataTransferException(message: 'toArray() input must return an array.');
            }

            return new self(values: $array);
        }

        return new self(values: get_object_vars(object: $input));
    }

    public function all() : array
    {
        return $this->values;
    }

    public function has(string $key) : bool
    {
        return array_key_exists(key: $key, array: $this->values);
    }

    public function get(string $key) : mixed
    {
        return $this->values[$key] ?? null;
    }
}
