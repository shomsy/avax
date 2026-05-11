<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\System\Flows\RunArchitectureTests;

use Avax\Components\SystemDesign\System\Capabilities\ArchitectureTesting\ArchitectureTest;
use Avax\Components\SystemDesign\System\Capabilities\Capacity\CapacityModel;
use Avax\Components\SystemDesign\System\Capabilities\Messaging\MessagingModel;
use Avax\Components\SystemDesign\System\Capabilities\SchemaValidation\NativeYamlParser;

/**
 * Runs architecture tests from an architecture-tests.yaml file against system models.
 *
 * @experimental V3 labs
 */
final class RunArchitectureTests
{
    private NativeYamlParser $yamlParser;

    public function __construct(NativeYamlParser|null $yamlParser = null)
    {
        $this->yamlParser = $yamlParser ?? new NativeYamlParser();
    }

    /**
     * Execute all architecture tests against capacity and messaging models.
     *
     * @return array{
     *     file: string,
     *     total: int,
     *     passed: int,
     *     failed: int,
     *     critical_failures: int,
     *     tests: list<array{
     *         test: string,
     *         severity: string,
     *         passed: bool,
     *         detail: string,
     *     }>,
     * }
     */
    public function execute(
        string          $testsPath,
        CapacityModel $capacity, MessagingModel|null $messaging = null,
    ) : array
    {
        $config = $this->yamlParser->parseFile($testsPath);

        $testConfigs = $config['assertions'] ?? [];
        $tests       = [];

        foreach ($testConfigs as $testConfig) {
            if (is_array($testConfig)) {
                $tests[] = ArchitectureTest::fromConfig($testConfig);
            }
        }

        $results          = [];
        $passed           = 0;
        $failed           = 0;
        $criticalFailures = 0;

        foreach ($tests as $test) {
            $result    = $test->evaluate($capacity, $messaging);
            $results[] = $result;

            if ($result['passed']) {
                $passed++;
            } else {
                $failed++;

                if ($result['severity'] === 'critical') {
                    $criticalFailures++;
                }
            }
        }

        return [
            'file'              => $testsPath,
            'total'             => count($tests),
            'passed'            => $passed,
            'failed'            => $failed,
            'critical_failures' => $criticalFailures,
            'tests'             => $results,
        ];
    }
}
