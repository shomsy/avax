<?php

declare(strict_types=1);

/**
 * Documentation Auto-Sync Script
 *
 * Automatically generates and updates router documentation by parsing
 * PHPDoc annotations and codebase structure. Maintains up-to-date
 * architecture diagrams, exception lists, and API documentation.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Avax\HTTP\Router\Routing\Exceptions\RouterExceptionInterface;
use ReflectionClass;
use ReflectionException;

class DocsSync
{
    private string $docsDir;
    private string $routerDir;

    public function __construct()
    {
        $this->docsDir = __DIR__ . '/../docs/Router';
        $this->routerDir = __DIR__ . '/../';
    }

    public function sync(): void
    {
        echo "🔄 Starting documentation sync...\n";

        $this->ensureDocsDir();
        $this->generateArchitectureDiagram();
        $this->generateExceptionList();
        $this->generateApiReference();

        echo "✅ Documentation sync completed!\n";
    }

    private function ensureDocsDir(): void
    {
        if (!is_dir($this->docsDir)) {
            mkdir($this->docsDir, 0755, true);
        }
    }

    private function generateArchitectureDiagram(): void
    {
        $diagram = $this->buildMermaidDiagram();
        $file = $this->docsDir . '/Architecture.md';

        $content = "# HTTP Router Architecture\n\n";
        $content .= "## System Overview\n\n";
        $content .= "```mermaid\n";
        $content .= $diagram;
        $content .= "\n```\n\n";
        $content .= "## Architecture Principles\n\n";
        $content .= "- **Single Responsibility**: Each component has one clear purpose\n";
        $content .= "- **Dependency Injection**: No tight coupling between layers\n";
        $content .= "- **Interface Segregation**: Clean contracts between components\n";
        $content .= "- **Thread Safety**: Components safe for concurrent access\n\n";
        $content .= "*Generated automatically on " . date('Y-m-d H:i:s') . "*\n";

        file_put_contents($file, $content);
        echo "📊 Generated architecture diagram\n";
    }

    private function buildMermaidDiagram(): string
    {
        $diagram = "graph TB\n";
        $diagram .= "    A[HTTP Request] --> B{Router.resolve}\n";
        $diagram .= "    B --> C{RouterKernel.handle}\n";
        $diagram .= "    C --> D{HttpRequestRouter.match}\n";
        $diagram .= "    D --> E[RouteDefinition]\n";
        $diagram .= "    E --> F[Controller/Action]\n";
        $diagram .= "    F --> G[Response]\n\n";
        $diagram .= "    D --> H{No Match?}\n";
        $diagram .= "    H --> I[FallbackManager]\n";
        $diagram .= "    I --> J[404 Response]\n\n";
        $diagram .= "    C --> K[Middleware Pipeline]\n";
        $diagram .= "    K --> L[RoutePipeline]\n\n";
        $diagram .= "    M[RouteBootstrapper] --> N[RouteCollection]\n";
        $diagram .= "    N --> O[CachedRouteLoader]\n";
        $diagram .= "    O --> P[DiskRouteLoader]\n\n";
        $diagram .= "    Q[RouterTrace] --> R[Performance Monitoring]\n";
        $diagram .= "    S[RouterMetricsCollector] --> T[Prometheus Export]\n\n";
        $diagram .= "    classDef core fill:#e1f5fe\n";
        $diagram .= "    classDef routing fill:#f3e5f5\n";
        $diagram .= "    classDef middleware fill:#e8f5e8\n";
        $diagram .= "    classDef monitoring fill:#fff3e0\n\n";
        $diagram .= "    class B,C,D core\n";
        $diagram .= "    class E,F routing\n";
        $diagram .= "    class K,L middleware\n";
        $diagram .= "    class Q,R,S,T monitoring\n";

        return $diagram;
    }

    private function generateExceptionList(): void
    {
        $exceptions = $this->scanExceptions();
        $file = $this->docsDir . '/Failure-Modes.md';

        $content = "# Router Failure Modes & Exceptions\n\n";
        $content .= "## Exception Hierarchy\n\n";
        $content .= "```\n";
        $content .= "RouterExceptionInterface\n";
        $content .= "├── RouterException (abstract)\n";

        foreach ($exceptions as $exception) {
            $content .= "├── {$exception['class']}\n";
        }

        $content .= "```\n\n";
        $content .= "## Detailed Exception Reference\n\n";

        foreach ($exceptions as $exception) {
            $content .= "### {$exception['class']}\n\n";
            $content .= "- **HTTP Status**: {$exception['http_status']}\n";
            $content .= "- **Retryable**: " . ($exception['retryable'] ? 'Yes' : 'No') . "\n";
            $content .= "- **Description**: {$exception['description']}\n\n";
        }

        $content .= "*Generated automatically on " . date('Y-m-d H:i:s') . "*\n";

        file_put_contents($file, $content);
        echo "📋 Generated exception reference\n";
    }

    private function scanExceptions(): array
    {
        $exceptions = [];
        $exceptionFiles = glob($this->routerDir . '/Routing/Exceptions/*.php');

        foreach ($exceptionFiles as $file) {
            $className = basename($file, '.php');
            $fullClass = "Avax\\HTTP\\Router\\Routing\\Exceptions\\{$className}";

            if (class_exists($fullClass)) {
                try {
                    $reflection = new ReflectionClass($fullClass);

                    if ($reflection->implementsInterface(RouterExceptionInterface::class)) {
                        $instance = $reflection->newInstanceWithoutConstructor();

                        $exceptions[] = [
                            'class' => $className,
                            'http_status' => method_exists($instance, 'getHttpStatusCode')
                                ? $instance->getHttpStatusCode()
                                : 'Unknown',
                            'retryable' => method_exists($instance, 'isRetryable')
                                ? ($instance->isRetryable() ? 'Yes' : 'No')
                                : 'Unknown',
                            'description' => $this->extractClassDescription($reflection),
                        ];
                    }
                } catch (ReflectionException) {
                    // Skip classes that can't be reflected
                }
            }
        }

        return $exceptions;
    }

    private function extractClassDescription(ReflectionClass $reflection): string
    {
        $docComment = $reflection->getDocComment();
        if ($docComment) {
            // Extract first line of class docblock
            $lines = explode("\n", $docComment);
            foreach ($lines as $line) {
                $line = trim($line, " \t/*");
                if (!empty($line)) {
                    return $line;
                }
            }
        }

        return "Exception for routing operations";
    }

    private function generateApiReference(): void
    {
        $interfaces = $this->scanInterfaces();
        $file = $this->docsDir . '/Api-Reference.md';

        $content = "# Router API Reference\n\n";
        $content .= "## Core Interfaces\n\n";

        foreach ($interfaces as $interface) {
            $content .= "### {$interface['name']}\n\n";
            $content .= "**Namespace:** `{$interface['namespace']}`\n\n";

            if (!empty($interface['description'])) {
                $content .= "**Description:** {$interface['description']}\n\n";
            }

            if (!empty($interface['methods'])) {
                $content .= "**Methods:**\n\n";
                foreach ($interface['methods'] as $method) {
                    $content .= "- `{$method['signature']}`\n";
                    if (!empty($method['description'])) {
                        $content .= "  - {$method['description']}\n";
                    }
                }
                $content .= "\n";
            }
        }

        $content .= "*Generated automatically on " . date('Y-m-d H:i:s') . "*\n";

        file_put_contents($file, $content);
        echo "📖 Generated API reference\n";
    }

    private function scanInterfaces(): array
    {
        $interfaces = [];
        $interfaceFiles = [
            $this->routerDir . '/RouterInterface.php',
            $this->routerDir . '/RouterRuntimeInterface.php',
            $this->routerDir . '/Routing/RouteSourceLoaderInterface.php',
            $this->routerDir . '/Routing/Exceptions/RouterExceptionInterface.php',
        ];

        foreach ($interfaceFiles as $file) {
            if (file_exists($file)) {
                $content = file_get_contents($file);
                $interfaces[] = $this->parseInterface($content, $file);
            }
        }

        return array_filter($interfaces);
    }

    private function parseInterface(string $content, string $file): array
    {
        $interface = [
            'name' => basename($file, '.php'),
            'namespace' => 'Avax\\HTTP\\Router',
            'description' => '',
            'methods' => [],
        ];

        // Extract namespace
        if (preg_match('/namespace\s+([^;]+);/', $content, $matches)) {
            $interface['namespace'] = $matches[1];
        }

        // Extract interface description
        if (preg_match('/\/\*\*\s*\n\s*\*\s*([^*\n]+)/', $content, $matches)) {
            $interface['description'] = trim($matches[1]);
        }

        // Extract methods
        preg_match_all('/public\s+function\s+([^\(]+)\([^)]*\)/', $content, $methodMatches);
        foreach ($methodMatches[0] as $methodSignature) {
            $interface['methods'][] = [
                'signature' => $methodSignature,
                'description' => '',
            ];
        }

        return $interface;
    }
}

// Run the sync if called directly
if ($argv[0] === __FILE__) {
    $sync = new DocsSync();
    $sync->sync();
}