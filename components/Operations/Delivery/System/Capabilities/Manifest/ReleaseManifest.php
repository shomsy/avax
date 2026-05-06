<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Delivery\System\Capabilities\Manifest;

class ReleaseManifest
{
    /**
     * @var array<int, array{name: string, action: callable}>
     */
    private array $steps = [];

    public function addStep(string $name, callable $action): self
    {
        $this->steps[] = ['name' => $name, 'action' => $action];

        return $this;
    }

    /**
     * @return array<int, array{name: string, action: callable}>
     */
    public function getSteps(): array
    {
        return $this->steps;
    }

    /**
     * @return array{steps: array<int, string>, created_at: string}
     */
    public function toArray(): array
    {
        return [
            'steps' => array_map(fn (array $s): string => $s['name'], $this->steps),
            'created_at' => date('c'),
        ];
    }
}
