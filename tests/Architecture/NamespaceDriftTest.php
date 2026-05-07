<?php

declare(strict_types=1);

namespace Avax\Tests\Architecture;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Ensures all classes use correct namespaces according to their location.
 *
 * Rules:
 * - framework/System/* -> Avax\Framework\System\*
 * - components/<Suite>/<Component>/System/* -> Avax\Components\<Suite>\<Component>\System\*
 */
final class NamespaceDriftTest extends TestCase
{
    private string $projectRoot;

    #[Test]
    public function framework_classes_use_correct_namespace(): void
    {
        $frameworkPath = $this->projectRoot.'/framework/System';
        $drifts = $this->findNamespaceDrifts($frameworkPath, 'Avax\\Framework\\System');

        $this->assertEmpty(
            $drifts,
            "Framework classes should use Avax\\Framework\\System namespace. Drifts:\n" . implode("\n", $drifts),
        );
    }

    /**
     * Scan PHP files in a directory and check if their namespace matches the expected prefix.
     *
     * @return list<string>
     */
    private function findNamespaceDrifts(string $directory, string $expectedNamespacePrefix): array
    {
        $drifts = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
        );

        /** @var RecursiveDirectoryIterator $file */
        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $content = file_get_contents($file->getPathname());
            if ($content === false) {
                continue;
            }

            // Extract namespace declaration
            if (preg_match('/^namespace\s+([^;]+);/m', $content, $matches)) {
                $actualNamespace = $matches[1];
                if (! str_starts_with($actualNamespace, $expectedNamespacePrefix)) {
                    $relativePath = str_replace($this->projectRoot.'/', '', $file->getPathname());
                    $drifts[] = "{$relativePath}: declares '{$actualNamespace}', expected prefix '{$expectedNamespacePrefix}'";
                }
            }
        }

        return $drifts;
    }

    #[Test]
    public function application_text_classes_use_correct_namespace(): void
    {
        $componentPath = $this->projectRoot.'/components/Application/Text/System';
        $drifts = $this->findNamespaceDrifts($componentPath, 'Avax\\Components\\Application\\Text\\System');

        $this->assertEmpty(
            $drifts,
            "Application/Text classes should use Avax\\Components\\Application\\Text\\System namespace. Drifts:\n" . implode("\n", $drifts),
        );
    }

    #[Test]
    public function application_validation_classes_use_correct_namespace(): void
    {
        $componentPath = $this->projectRoot.'/components/Application/Validation/System';
        $drifts = $this->findNamespaceDrifts($componentPath, 'Avax\\Components\\Application\\Validation\\System');

        $this->assertEmpty(
            $drifts,
            "Application/Validation classes should use Avax\\Components\\Application\\Validation\\System namespace. Drifts:\n" . implode("\n", $drifts),
        );
    }

    #[Test]
    public function http_session_classes_use_correct_namespace(): void
    {
        $componentPath = $this->projectRoot.'/components/HTTP/Session/System';
        $drifts = $this->findNamespaceDrifts($componentPath, 'Avax\\Components\\HTTP\\Session\\System');

        $this->assertEmpty(
            $drifts,
            "HTTP/Session classes should use Avax\\Components\\HTTP\\Session\\System namespace. Drifts:\n" . implode("\n", $drifts),
        );
    }

    #[Test]
    public function http_middleware_classes_use_correct_namespace(): void
    {
        $componentPath = $this->projectRoot.'/components/HTTP/Middleware/System';
        $drifts = $this->findNamespaceDrifts($componentPath, 'Avax\\Components\\HTTP\\Middleware\\System');

        $this->assertEmpty(
            $drifts,
            "HTTP/Middleware classes should use Avax\\Components\\HTTP\\Middleware\\System namespace. Drifts:\n" . implode("\n", $drifts),
        );
    }

    #[Test]
    public function http_response_classes_use_correct_namespace(): void
    {
        $componentPath = $this->projectRoot.'/components/HTTP/Response/System';
        $drifts = $this->findNamespaceDrifts($componentPath, 'Avax\\Components\\HTTP\\Response\\System');

        $this->assertEmpty(
            $drifts,
            "HTTP/Response classes should use Avax\\Components\\HTTP\\Response\\System namespace. Drifts:\n" . implode("\n", $drifts),
        );
    }

    #[Test]
    public function operations_events_classes_use_correct_namespace(): void
    {
        $componentPath = $this->projectRoot.'/components/Operations/Events/System';
        $drifts = $this->findNamespaceDrifts($componentPath, 'Avax\\Components\\Operations\\Events\\System');

        $this->assertEmpty(
            $drifts,
            "Operations/Events classes should use Avax\\Components\\Operations\\Events\\System namespace. Drifts:\n" . implode("\n", $drifts),
        );
    }

    #[Test]
    public function application_datetime_classes_use_correct_namespace(): void
    {
        $componentPath = $this->projectRoot.'/components/Application/DateTime/System';
        $drifts = $this->findNamespaceDrifts($componentPath, 'Avax\\Components\\Application\\DateTime\\System');

        $this->assertEmpty(
            $drifts,
            "Application/DateTime classes should use Avax\\Components\\Application\\DateTime\\System namespace. Drifts:\n" . implode("\n", $drifts),
        );
    }

    protected function setUp(): void
    {
        $this->projectRoot = dirname(__DIR__, 2);
    }
}
