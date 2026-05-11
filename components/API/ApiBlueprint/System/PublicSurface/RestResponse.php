<?php

declare(strict_types=1);

namespace Avax\Components\API\ApiBlueprint\System\PublicSurface;

final readonly class RestResponse
{
    /**
     * @param array<string, mixed> $data
     */
    public function __construct(
        public array             $data = [],
        public int               $status = 200,
        public RestResponseMeta|null $meta = null,
    ) {}

    public function toJson() : string
    {
        return json_encode($this->toArray(), JSON_THROW_ON_ERROR);
    }

    /**
     * @return array{data: array<string, mixed>, meta?: array<string, mixed>}
     */
    public function toArray() : array
    {
        $result = ['data' => $this->data];

        if ($this->meta !== null) {
            $result['meta'] = $this->meta->toArray();
        }

        return $result;
    }
}
