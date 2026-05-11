<?php

declare(strict_types=1);

namespace Avax\Components\API\ApiBlueprint\System\PublicSurface;

final readonly class RestResponseMeta
{
    public function __construct(
        public int  $total = 0,
        public int  $page = 1,
        public int  $perPage = 15,
        public int|null $lastPage = null,
    ) {}

    public function computedLastPage() : int
    {
        return $this->lastPage ?? (int) ceil($this->total / $this->perPage);
    }

    /**
     * @return array{total: int, page: int, per_page: int, last_page: int|null}
     */
    public function toArray() : array
    {
        return [
            'total'     => $this->total,
            'page'      => $this->page,
            'per_page'  => $this->perPage,
            'last_page' => $this->lastPage,
        ];
    }
}
