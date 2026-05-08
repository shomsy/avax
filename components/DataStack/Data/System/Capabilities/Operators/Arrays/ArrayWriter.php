<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Operators\Arrays;

final readonly class ArrayWriter
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function set(array &$data, string $key, mixed $value): void
    {
        $data[$key] = $value;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function setNested(array &$data, string $path, mixed $value): void
    {
        $keys = explode(separator: '.', string: $path);
        $current = &$data;

        foreach ($keys as $key) {
            if (! isset($current[$key]) || ! is_array($current[$key])) {
                $current[$key] = [];
            }

            $current = &$current[$key];
        }

        $current = $value;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function forget(array &$data, string $key): void
    {
        unset($data[$key]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function forgetNested(array &$data, string $path): void
    {
        $keys = explode(separator: '.', string: $path);
        $current = &$data;
        $lastKey = array_pop($keys);

        foreach ($keys as $key) {
            if (! isset($current[$key]) || ! is_array($current[$key])) {
                return;
            }

            $current = &$current[$key];
        }

        unset($current[$lastKey]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function push(array &$data, string $key, mixed $value): void
    {
        if (! isset($data[$key]) || ! is_array($data[$key])) {
            $data[$key] = [];
        }

        $data[$key][] = $value;
    }
}
