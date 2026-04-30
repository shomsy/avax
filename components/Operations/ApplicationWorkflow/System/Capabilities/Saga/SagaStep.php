<?php
declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\Saga;

final readonly class SagaStep
{
    public function __construct(
        public string $name,
        public callable $action,
        public callable|null $compensation = null
    ) {}
}
