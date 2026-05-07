<?php

declare(strict_types=1);

namespace Avax\Tests\Architecture;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Verifies that each component follows the expected directory structure:
 * System/PublicSurface, System/Flows, System/Capabilities, System/Configuration, System/Foundation
 */
final class ComponentStructureTest extends TestCase
{
    private string $componentsPath;

    #[Test]
    public function application_text_has_required_structure(): void
    {
        $componentPath = $this->componentsPath.'/Application/Text';

        $this->assertDirectoryExists($componentPath.'/System/PublicSurface');
        $this->assertDirectoryExists($componentPath.'/System/Capabilities');
    }

    #[Test]
    public function application_validation_has_required_structure(): void
    {
        $componentPath = $this->componentsPath.'/Application/Validation';

        $this->assertDirectoryExists($componentPath.'/System/PublicSurface');
        $this->assertDirectoryExists($componentPath.'/System/Capabilities');
        $this->assertDirectoryExists($componentPath.'/System/Flows');
        $this->assertDirectoryExists($componentPath.'/System/Configuration');
        $this->assertDirectoryExists($componentPath.'/System/Foundation');
    }

    #[Test]
    public function application_container_has_required_structure(): void
    {
        $componentPath = $this->componentsPath.'/Application/Container';

        $this->assertDirectoryExists($componentPath.'/System/PublicSurface');
        $this->assertDirectoryExists($componentPath.'/System/Capabilities');
        $this->assertDirectoryExists($componentPath.'/System/Foundation');
    }

    #[Test]
    public function application_datetime_has_required_structure(): void
    {
        $componentPath = $this->componentsPath.'/Application/DateTime';

        $this->assertDirectoryExists($componentPath.'/System/PublicSurface');
        $this->assertDirectoryExists($componentPath.'/System/Capabilities');
        $this->assertDirectoryExists($componentPath.'/System/Flows');
        $this->assertDirectoryExists($componentPath.'/System/Configuration');
        $this->assertDirectoryExists($componentPath.'/System/Foundation');
    }

    #[Test]
    public function http_session_has_required_structure(): void
    {
        $componentPath = $this->componentsPath.'/HTTP/Session';

        $this->assertDirectoryExists($componentPath.'/System/PublicSurface');
        $this->assertDirectoryExists($componentPath.'/System/Capabilities');
        $this->assertDirectoryExists($componentPath.'/System/Flows');
        $this->assertDirectoryExists($componentPath.'/System/Foundation');
    }

    #[Test]
    public function http_middleware_has_required_structure(): void
    {
        $componentPath = $this->componentsPath.'/HTTP/Middleware';

        $this->assertDirectoryExists($componentPath.'/System/PublicSurface');
        $this->assertDirectoryExists($componentPath.'/System/Capabilities');
        $this->assertDirectoryExists($componentPath.'/System/Flows');
        $this->assertDirectoryExists($componentPath.'/System/Configuration');
        $this->assertDirectoryExists($componentPath.'/System/Foundation');
    }

    #[Test]
    public function http_response_has_required_structure(): void
    {
        $componentPath = $this->componentsPath.'/HTTP/Response';

        $this->assertDirectoryExists($componentPath.'/System/PublicSurface');
        $this->assertDirectoryExists($componentPath.'/System/Capabilities');
        $this->assertDirectoryExists($componentPath.'/System/Flows');
        $this->assertDirectoryExists($componentPath.'/System/Configuration');
        $this->assertDirectoryExists($componentPath.'/System/Foundation');
    }

    #[Test]
    public function http_router_has_required_structure(): void
    {
        $componentPath = $this->componentsPath.'/HTTP/Router';

        $this->assertDirectoryExists($componentPath.'/System');
    }

    #[Test]
    public function operations_events_has_required_structure(): void
    {
        $componentPath = $this->componentsPath.'/Operations/Events';

        $this->assertDirectoryExists($componentPath.'/System/PublicSurface');
        $this->assertDirectoryExists($componentPath.'/System/Capabilities');
        $this->assertDirectoryExists($componentPath.'/System/Flows');
        $this->assertDirectoryExists($componentPath.'/System/Configuration');
        $this->assertDirectoryExists($componentPath.'/System/Foundation');
    }

    #[Test]
    public function operations_logging_has_required_structure(): void
    {
        $componentPath = $this->componentsPath.'/Operations/Logging';

        $this->assertDirectoryExists($componentPath.'/System');
    }

    #[Test]
    public function identity_auth_has_required_structure(): void
    {
        $componentPath = $this->componentsPath.'/Identity/Auth';

        $this->assertDirectoryExists($componentPath.'/System');
    }

    #[Test]
    public function presentation_view_has_required_structure(): void
    {
        $componentPath = $this->componentsPath.'/Presentation/View';

        $this->assertDirectoryExists($componentPath.'/System');
    }

    #[Test]
    public function datastack_database_has_required_structure(): void
    {
        $componentPath = $this->componentsPath.'/DataStack/Database';

        $this->assertDirectoryExists($componentPath.'/System');
    }

    #[Test]
    public function framework_system_has_required_structure(): void
    {
        $frameworkPath = dirname(__DIR__, 2).'/framework/System';

        $this->assertDirectoryExists($frameworkPath.'/PublicSurface');
        $this->assertDirectoryExists($frameworkPath.'/Flows');
        $this->assertDirectoryExists($frameworkPath.'/Capabilities');
        $this->assertDirectoryExists($frameworkPath.'/Configuration');
        $this->assertDirectoryExists($frameworkPath.'/Foundation');
    }

    #[Test]
    public function component_system_directories_contain_php_files(): void
    {
        $components = [
            'Application/Text',
            'Application/Validation',
            'Application/Container',
            'Application/DateTime',
            'HTTP/Session',
            'HTTP/Middleware',
            'HTTP/Response',
            'Operations/Events',
        ];

        foreach ($components as $component) {
            $systemPath = $this->componentsPath.'/'.$component.'/System';
            $this->assertDirectoryExists(
                $systemPath,
                "System directory should exist for {$component}",
            );

            $phpFiles = glob($systemPath.'/**/*.php', GLOB_NOSORT);
            $this->assertNotEmpty(
                $phpFiles,
                "System directory should contain PHP files for {$component}",
            );
        }
    }

    protected function setUp(): void
    {
        $this->componentsPath = dirname(__DIR__, 2).'/components';
    }
}
