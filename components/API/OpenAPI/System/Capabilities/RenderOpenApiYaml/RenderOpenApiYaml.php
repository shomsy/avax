<?php

declare(strict_types=1);

namespace Avax\Components\API\OpenAPI\System\Capabilities\RenderOpenApiYaml;

use Avax\Components\API\OpenAPI\System\PublicSurface\OpenApiDocument;

final readonly class RenderOpenApiYaml
{
    public function render(OpenApiDocument $document) : string
    {
        return rtrim($this->dump(value: $document->toArray())) . PHP_EOL;
    }

    private function dump(mixed $value, int $indent = 0) : string
    {
        if (is_array($value)) {
            return $this->dumpArray(value: $value, indent: $indent);
        }

        return str_repeat(' ', $indent) . $this->scalar(value: $value) . PHP_EOL;
    }

    /**
     * @param array<mixed> $value
     */
    private function dumpArray(array $value, int $indent) : string
    {
        $lines  = '';
        $isList = array_is_list($value);

        foreach ($value as $key => $item) {
            $prefix = str_repeat(' ', $indent) . ($isList ? '- ' : (string) $key . ': ');

            if (is_array($item)) {
                $lines .= $prefix . PHP_EOL . $this->dump(value: $item, indent: $indent + 2);
            } else {
                $lines .= $prefix . $this->scalar(value: $item) . PHP_EOL;
            }
        }

        return $lines;
    }

    private function scalar(mixed $value) : string
    {
        return match (true) {
            $value === null                  => 'null',
            is_bool($value)                  => $value ? 'true' : 'false',
            is_int($value), is_float($value) => (string) $value,
            default                          => json_encode((string) $value, JSON_UNESCAPED_SLASHES) ?: '""',
        };
    }
}
