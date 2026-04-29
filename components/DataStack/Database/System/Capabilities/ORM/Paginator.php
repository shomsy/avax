<?php
declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\ORM;

final readonly class Paginator
{
    public function __construct(
        public array $items,
        public int $total,
        public int $perPage,
        public int $currentPage
    ) {}

    public function lastPage(): int
    {
        return (int) ceil($this->total / $this->perPage);
    }

    public function hasMorePages(): bool
    {
        return $this->currentPage < $this->lastPage();
    }
}
