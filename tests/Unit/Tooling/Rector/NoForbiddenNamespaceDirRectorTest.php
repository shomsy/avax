<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Tooling\Rector;

use Avax\Tooling\Rector\NoForbiddenNamespaceDirRector\NoForbiddenNamespaceDirRector;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Namespace_;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class NoForbiddenNamespaceDirRectorTest extends TestCase
{
    #[Test]
    public function ruleIsConfigured() : void
    {
        $rector = new NoForbiddenNamespaceDirRector();
        self::assertInstanceOf(NoForbiddenNamespaceDirRector::class, $rector);
        self::assertContains(Namespace_::class, $rector->getNodeTypes());
    }

    #[Test]
    public function ruleDefinitionExists() : void
    {
        $rector     = new NoForbiddenNamespaceDirRector();
        $definition = $rector->getRuleDefinition();

        self::assertStringContainsStringIgnoringCase('forbids', $definition->getDescription());
        self::assertStringContainsString('Services', $definition->getDescription());
    }

    #[Test]
    public function rectorConfigIncludesCustomRule() : void
    {
        $contents = file_get_contents(__DIR__ . '/../../../../rector.php');
        self::assertStringContainsString('NoForbiddenNamespaceDirRector', $contents);
        self::assertStringContainsString('$rectorConfig->rule(', $contents);
    }

    #[Test]
    public function ruleDetectsForbiddenServicesNamespace() : void
    {
        $rector = new NoForbiddenNamespaceDirRector();

        $namespace = new Namespace_(new Name('Avax\Components\Data\Services'));
        $result    = $rector->refactor($namespace);

        // Rule should return null (no auto-fix) but should have added an error
        self::assertNull($result);
    }

    #[Test]
    public function ruleAllowsCleanNamespace() : void
    {
        $rector = new NoForbiddenNamespaceDirRector();

        $namespace = new Namespace_(new Name('Avax\Components\Data\System\Capabilities\StoreObjects'));
        $result    = $rector->refactor($namespace);

        self::assertNull($result);
    }

    #[Test]
    public function forbiddenSegmentsListIsNonEmpty() : void
    {
        $rector = new NoForbiddenNamespaceDirRector();

        // Use reflection to access the private constant
        $reflection = new ReflectionClass($rector);
        $constants  = $reflection->getConstants();

        self::assertArrayHasKey('FORBIDDEN_SEGMENTS', $constants);
        self::assertIsArray($constants['FORBIDDEN_SEGMENTS']);
        self::assertGreaterThanOrEqual(10, count($constants['FORBIDDEN_SEGMENTS']));
    }
}
