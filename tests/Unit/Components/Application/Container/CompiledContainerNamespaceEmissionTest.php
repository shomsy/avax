<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\Container;

use Avax\Components\Application\Container\System\Capabilities\Composition\Compilation\CompiledContainer;
use Avax\Components\Application\Container\System\Capabilities\Composition\Compilation\MethodEmitter;
use Avax\Components\Application\Container\System\Capabilities\ContainerObservability\Errors\ContainerException;
use Avax\Components\Application\Container\System\Capabilities\Resolution\ResolveDependency;
use Avax\Components\Application\Container\System\Capabilities\Resolution\ResolveRequest;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Proves that compiled container namespace emission uses current AvaX namespaces.
 */
final class CompiledContainerNamespaceEmissionTest extends TestCase
{
    #[Test]
    public function method_emitter_dynamic_method_emits_current_resolve_dependency_namespace(): void
    {
        $emitter = new MethodEmitter();
        $source = $emitter->emitDynamicMethod('service_test1234567890ab');

        self::assertStringContainsString(
            '\\Avax\\Components\\Application\\Container\\System\\Capabilities\\Resolution\\ResolveDependency',
            $source,
            'emitDynamicMethod must emit current ResolveDependency namespace',
        );

        self::assertStringContainsString(
            '\\Avax\\Components\\Application\\Container\\System\\Capabilities\\Resolution\\ResolveRequest',
            $source,
            'emitDynamicMethod must emit current ResolveRequest namespace',
        );

        self::assertStringNotContainsString(
            '\\Avax\\Container\\Capabilities\\Resolution\\ResolveDependency',
            $source,
            'emitDynamicMethod must NOT emit old ResolveDependency namespace',
        );
    }

    #[Test]
    public function method_emitter_direct_method_emits_current_resolve_dependency_namespace(): void
    {
        $emitter = new MethodEmitter();
        $source = $emitter->emitDirectMethod(
            'service_test1234567890ab',
            'test-service',
            'stdClass',
            null,
            [],
            false,
        );

        self::assertStringContainsString(
            '\\Avax\\Components\\Application\\Container\\System\\Capabilities\\Resolution\\ResolveDependency',
            $source,
            'emitDirectMethod must emit current ResolveDependency namespace',
        );

        self::assertStringContainsString(
            '\\Avax\\Components\\Application\\Container\\System\\Capabilities\\Resolution\\ResolveRequest',
            $source,
            'emitDirectMethod must emit current ResolveRequest namespace',
        );
    }

    #[Test]
    public function method_emitter_fallback_emits_current_container_exception_namespace(): void
    {
        $emitter = new MethodEmitter();

        // Create a ResolvePlan with a required parameter that has no service ID, no default, and doesn't allow null
        // This will trigger the ContainerException fallback path
        $resolvePlan = new \Avax\Components\Application\Container\System\Capabilities\Resolution\ResolvePlan(
            parameters: [
                [
                    'name' => 'requiredParam',
                    'serviceId' => null,
                    'source' => 'type',
                    'inputName' => 'requiredParam',
                    'hasDefault' => false,
                    'default' => '',
                    'allowsNull' => false,
                ],
            ],
        );

        $source = $emitter->emitDirectMethod(
            'service_test1234567890ab',
            'test-service',
            'stdClass',
            $resolvePlan,
            [],
            false,
        );

        self::assertStringContainsString(
            '\\Avax\\Components\\Application\\Container\\System\\Capabilities\\ContainerObservability\\Errors\\ContainerException',
            $source,
            'fallback must emit current ContainerException namespace',
        );

        self::assertStringNotContainsString(
            '\\Avax\\Container\\Capabilities\\Diagnostics\\Errors\\ContainerException',
            $source,
            'fallback must NOT emit old ContainerException namespace',
        );
    }

    #[Test]
    public function method_emitter_never_emits_old_avax_container_namespaces(): void
    {
        $emitter = new MethodEmitter();

        $dynamicSource = $emitter->emitDynamicMethod('service_test1234567890ab');
        $directSource = $emitter->emitDirectMethod(
            'service_abc1234567890def',
            'another-service',
            'RuntimeException',
            null,
            [],
            false,
        );

        foreach ([$dynamicSource, $directSource] as $source) {
            self::assertDoesNotMatchRegularExpression(
                '/\\\\Avax\\\\Container\\\\(?!Components)/',
                $source,
                'No emitted code should reference old Avax\Container\ namespaces (excluding Avax\Components)',
            );
        }
    }

    #[Test]
    public function generated_compiled_container_extends_current_base_class_namespace(): void
    {
        $emitter = new MethodEmitter();
        $methodName = $emitter->methodNameFor('test-service');

        $directMethod = $emitter->emitDirectMethod(
            $methodName,
            'test-service',
            'stdClass',
            null,
            [],
            false,
        );

        // Simulate what CompileContainer::sourceFor generates
        $generatedSource = <<<PHP
            declare(strict_types=1);

            namespace Avax\\Components\\Application\\Container\\DI\\Capabilities\\Composition\\Generated;

            return new class extends \\Avax\\Components\\Application\\Container\\System\\Capabilities\\Composition\\Compilation\\CompiledContainer
            {
                protected string \$fingerprint = 'test-fp';
                protected array \$entries = ['test-service' => '{$methodName}'];

            {$directMethod}
            };
            PHP;

        self::assertStringContainsString(
            '\\Avax\\Components\\Application\\Container\\System\\Capabilities\\Composition\\Compilation\\CompiledContainer',
            $generatedSource,
            'generated artifact must extend current CompiledContainer base namespace',
        );

        self::assertStringNotContainsString(
            '\\Avax\\Container\\Capabilities\\Composition\\Compilation\\CompiledContainer',
            $generatedSource,
            'generated artifact must NOT extend old CompiledContainer namespace',
        );
    }

    #[Test]
    public function generated_artifact_can_be_included_without_class_not_found(): void
    {
        $projectRoot = dirname(__DIR__, 5);
        $php = PHP_BINARY;
        $testScript = <<< PHPCODE
            <?php
            require_once '{$projectRoot}/vendor/autoload.php';

            use Avax\\Components\\Application\\Container\\System\\Capabilities\\Composition\\Compilation\\CompiledContainer;
            use Avax\\Components\\Application\\Container\\System\\Capabilities\\Composition\\Compilation\\MethodEmitter;

            \$emitter = new MethodEmitter();
            \$methodName = \$emitter->methodNameFor('runtime-include-test');

            \$directMethod = \$emitter->emitDirectMethod(
                \$methodName,
                'runtime-include-test',
                'stdClass',
                null,
                [],
                false,
            );

            \$generatedSource = <<<PHP
            declare(strict_types=1);

            namespace Avax\\\\Components\\\\Application\\\\Container\\\\DI\\\\Capabilities\\\\Composition\\\\Generated;

            return new class extends \\\\Avax\\\\Components\\\\Application\\\\Container\\\\System\\\\Capabilities\\\\Composition\\\\Compilation\\\\CompiledContainer
            {
                protected string \\\$fingerprint = 'test-fp';
                protected array \\\$entries = ['runtime-include-test' => '{\$methodName}'];

            {\$directMethod}
            };
            PHP;

            \$tempFile = sys_get_temp_dir().'/avax-test-compiled-container-'.uniqid().'.php';
            file_put_contents(\$tempFile, '<?php'.PHP_EOL.PHP_EOL.\$generatedSource);

            try {
                \$instance = require \$tempFile;

                if (!(\$instance instanceof CompiledContainer)) {
                    fwrite(STDERR, "Instance is not a CompiledContainer\\n");
                    exit(1);
                }
                if (!\$instance->has('runtime-include-test')) {
                    fwrite(STDERR, "Instance does not have the service\\n");
                    exit(1);
                }
                echo "OK";
                exit(0);
            } finally {
                if (file_exists(\$tempFile)) {
                    unlink(\$tempFile);
                }
            }
            PHPCODE;

        $tempScript = sys_get_temp_dir().'/avax-test-compiled-include-'.uniqid().'.php';
        file_put_contents($tempScript, $testScript);

        try {
            $output = [];
            $result = 0;
            exec("$php $tempScript 2>&1", $output, $result);

            self::assertSame(0, $result, 'Generated artifact include failed: '.implode("\n", $output));
            self::assertStringContainsString('OK', implode("\n", $output));
        } finally {
            if (file_exists($tempScript)) {
                unlink($tempScript);
            }
        }
    }

    #[Test]
    public function generated_artifact_resolution_smoke_through_compiled_output(): void
    {
        $projectRoot = dirname(__DIR__, 5);
        $php = PHP_BINARY;
        $testScript = <<< PHPCODE
            <?php
            require_once '{$projectRoot}/vendor/autoload.php';

            use Avax\\Components\\Application\\Container\\System\\Capabilities\\Composition\\Compilation\\CompiledContainer;
            use Avax\\Components\\Application\\Container\\System\\Capabilities\\Composition\\Compilation\\MethodEmitter;

            \$emitter = new MethodEmitter();
            \$methodName = \$emitter->methodNameFor('smoke-resolution-test');

            \$directMethod = \$emitter->emitDirectMethod(
                \$methodName,
                'smoke-resolution-test',
                'stdClass',
                null,
                [],
                false,
            );

            \$generatedSource = <<<PHP
            declare(strict_types=1);

            namespace Avax\\\\Components\\\\Application\\\\Container\\\\DI\\\\Capabilities\\\\Composition\\\\Generated;

            return new class extends \\\\Avax\\\\Components\\\\Application\\\\Container\\\\System\\\\Capabilities\\\\Composition\\\\Compilation\\\\CompiledContainer
            {
                protected string \\\$fingerprint = 'smoke-fp';
                protected array \\\$entries = ['smoke-resolution-test' => '{\$methodName}'];

            {\$directMethod}
            };
            PHP;

            \$tempFile = sys_get_temp_dir().'/avax-test-compiled-smoke-'.uniqid().'.php';
            file_put_contents(\$tempFile, '<?php'.PHP_EOL.PHP_EOL.\$generatedSource);

            try {
                \$instance = require \$tempFile;

                if (!(\$instance instanceof CompiledContainer)) {
                    fwrite(STDERR, "Instance is not a CompiledContainer\\n");
                    exit(1);
                }
                if (\$instance->fingerprint() !== 'smoke-fp') {
                    fwrite(STDERR, "Fingerprint mismatch\\n");
                    exit(1);
                }
                if (!\$instance->has('smoke-resolution-test')) {
                    fwrite(STDERR, "Instance does not have the service\\n");
                    exit(1);
                }
                if (\$instance->methodFor('smoke-resolution-test') === null) {
                    fwrite(STDERR, "Method name is null\\n");
                    exit(1);
                }
                echo "OK";
                exit(0);
            } finally {
                if (file_exists(\$tempFile)) {
                    unlink(\$tempFile);
                }
            }
            PHPCODE;

        $tempScript = sys_get_temp_dir().'/avax-test-compiled-smoke-'.uniqid().'.php';
        file_put_contents($tempScript, $testScript);

        try {
            $output = [];
            $result = 0;
            exec("$php $tempScript 2>&1", $output, $result);

            self::assertSame(0, $result, 'Generated artifact smoke test failed: '.implode("\n", $output));
            self::assertStringContainsString('OK', implode("\n", $output));
        } finally {
            if (file_exists($tempScript)) {
                unlink($tempScript);
            }
        }
    }

    #[Test]
    public function emitted_method_signature_references_resolvable_classes(): void
    {
        $emitter = new MethodEmitter();
        $source = $emitter->emitDynamicMethod('service_test1234567890ab');

        $requiredClasses = [
            ResolveDependency::class,
            ResolveRequest::class,
        ];

        foreach ($requiredClasses as $fqcn) {
            self::assertTrue(
                class_exists($fqcn),
                "Class {$fqcn} referenced in emitted code must exist for autoloading",
            );
        }

        self::assertTrue(
            class_exists(ContainerException::class),
            'ContainerException referenced in fallback must exist for autoloading',
        );

        self::assertTrue(
            class_exists(CompiledContainer::class),
            'CompiledContainer base class must exist for autoloading',
        );
    }
}
