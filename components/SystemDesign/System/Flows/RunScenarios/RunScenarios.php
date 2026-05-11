<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\System\Flows\RunScenarios;

use Avax\Components\SystemDesign\System\Capabilities\Capacity\CapacityModel;
use Avax\Components\SystemDesign\System\Capabilities\ScenarioRunner\Scenario;
use Avax\Components\SystemDesign\System\Capabilities\SchemaValidation\NativeYamlParser;

/**
 * Runs scenarios from a scenarios.yaml file against a capacity model.
 *
 * @experimental V3 labs
 */
final class RunScenarios
{
    private NativeYamlParser $yamlParser;

    public function __construct(NativeYamlParser|null $yamlParser = null)
    {
        $this->yamlParser = $yamlParser ?? new NativeYamlParser();
    }

    /**
     * Execute all scenarios from a scenarios file against a capacity model.
     *
     * @return array{
     *     file: string,
     *     total: int,
     *     passed: int,
     *     failed: int,
     *     scenarios: list<array{
     *         scenario: string,
     *         type: string,
     *         passed: bool,
     *         results: list<array{assertion: string, passed: bool, detail: string}>,
     *     }>,
     * }
     */
    public function execute(string $scenariosPath, CapacityModel $model) : array
    {
        $config = $this->yamlParser->parseFile($scenariosPath);

        $scenarioConfigs = $config['scenarios'] ?? [];
        $scenarios       = [];

        foreach ($scenarioConfigs as $scenarioConfig) {
            if (is_array($scenarioConfig)) {
                $scenarios[] = Scenario::fromConfig($scenarioConfig);
            }
        }

        $results = [];
        $passed  = 0;
        $failed  = 0;

        foreach ($scenarios as $scenario) {
            $result    = $scenario->evaluate($model);
            $results[] = $result;

            if ($result['passed']) {
                $passed++;
            } else {
                $failed++;
            }
        }

        return [
            'file'      => $scenariosPath,
            'total'     => count($scenarios),
            'passed'    => $passed,
            'failed'    => $failed,
            'scenarios' => $results,
        ];
    }
}
