<?php

declare(strict_types=1);

namespace Avax\Components\API\ApiBlueprint\System\Capabilities\RestApi;

final readonly class ApplyPagination
{
    public function __construct(
        public int $page = 1,
        public int $perPage = 15,
    ) {}

    public function hasMore(int $total) : bool
    {
        return $this->offset() + $this->perPage < $total;
    }

    public function offset() : int
    {
        return ($this->page - 1) * $this->perPage;
    }

    public function hasNextPage(int $total) : bool
    {
        return $this->page < $this->lastPage($total);
    }

    public function lastPage(int $total) : int
    {
        return (int) ceil($total / $this->perPage);
    }

    public function hasPreviousPage() : bool
    {
        return $this->page > 1;
    }
}
