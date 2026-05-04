<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\ArchitectureTests\System\Flows\RunArchitectureTests;

use Avax\Components\SystemDesign\ArchitectureTests\System\PublicSurface\ArchitectureTest;

final readonly class RunArchitectureTests
{
    /**
     * @param list<callable(): ArchitectureTest> $tests
     * @return list<ArchitectureTest>
     */
    public function __invoke(array $tests): array
    {
        $results = [];

        foreach ($tests as $test) {
            $results[] = $test();
        }

        return $results;
    }
}
