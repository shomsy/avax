<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\DataTransfer\System\Flows\SerializeDataObject;

use Avax\Components\DataStack\DataTransfer\System\Configuration\DataTransferConfig;

final readonly class ConvertDataObjectToJsonApi
{
    public function __construct(private DataTransferConfig|null $dataTransferConfig = null) {}

    /**
     * @return array{data: array{type: string, id: mixed, attributes: array<array-key, mixed>}}
     */
    public function convert(object $object, string $type) : array
    {
        $array = new ConvertDataObjectToArray(dataTransferConfig: $this->dataTransferConfig)->convert(object: $object);

        return [
            'data' => [
                'type'       => $type,
                'id'         => $array['id'] ?? null,
                'attributes' => $array,
            ],
        ];
    }
}
