<?php

declare(strict_types=1);

namespace Avax\Components\DeveloperTools\DumpDebugger\System\Capabilities\Formatters;

final class VariableFormatter
{
    public function formatJson(mixed $value) : string
    {
        $encoded = json_encode($value, JSON_PRETTY_PRINT);

        return $encoded === false ? 'null' : $encoded;
    }

    public function formatArray(mixed $value) : string
    {
        return print_r($value, true);
    }

    public function formatObject(object $value) : string
    {
        return $value::class . ' ' . json_encode($value, JSON_PRETTY_PRINT);
    }
}
