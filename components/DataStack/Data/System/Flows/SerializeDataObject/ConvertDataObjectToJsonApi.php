<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Flows\SerializeDataObject;

use Avax\Components\DataStack\Data\System\Capabilities\DataTransfer\Configuration\DataTransferConfig;

final readonly class ConvertDataObjectToJsonApi
{
    public function __construct(private ?DataTransferConfig $config = null) {}

    public function convert(object $object, string $type): array
    {
        $array = new ConvertDataObjectToArray(config: $this->config)->convert(object: $object);

        return [
            'data' => [
                'type' => $type,
                'id'   => $array['id'] ?? null,
                'attributes' => $array,
            ],
        ];
    }
}
