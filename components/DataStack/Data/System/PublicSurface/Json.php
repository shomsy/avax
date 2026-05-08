<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\PublicSurface;

use Avax\Components\DataStack\Data\System\Capabilities\Forms\ArrayForm\Arrhae;
use Avax\Components\DataStack\Data\System\Capabilities\Forms\JsonForm\Json as JsonForm;
use JsonException;

/**
 * Json — JSON document DSL public entry point.
 *
 * Json composes Arrhae after decode for fluent JSON document manipulation.
 */
final class Json
{
    private function __construct()
    {
    }

    /**
     * @throws JsonException
     */
    public static function decode(string $json): JsonForm
    {
        return JsonForm::decode(json: $json);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function from(array $data): JsonForm
    {
        return new JsonForm(arrhae: new Arrhae(items: $data));
    }
}
