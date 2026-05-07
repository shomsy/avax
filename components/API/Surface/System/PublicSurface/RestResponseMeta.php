<?php

declare(strict_types=1);

namespace Avax\Components\API\Surface\System\PublicSurface;

final readonly class RestResponseMeta
{
    public function __construct(
        public int  $total = 0,
        public int  $page = 1,
        public int  $perPage = 15,
        public ?int $lastPage = null,
    ) {}

    public function computedLastPage() : int
    {
        return $this->lastPage ?? (int) ceil($this->total / $this->perPage);
    }

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
