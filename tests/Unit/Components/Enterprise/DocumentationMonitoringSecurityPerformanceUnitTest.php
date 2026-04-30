<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Enterprise;

use Avax\Components\Documentation\Api\System\PublicSurface\ApiDocumentation;
use Avax\Components\Operations\Monitoring\System\Capabilities\Health\HealthCheckResult;
use Avax\Components\Operations\Monitoring\System\PublicSurface\Monitoring;
use Avax\Components\Performance\System\PublicSurface\Performance;
use Avax\Components\Security\System\Capabilities\SignedUrls\SignedUrlGenerator;
use Avax\Components\Security\System\PublicSurface\ResponseFormatter;
use Avax\Components\Security\System\PublicSurface\Security;
use Avax\Tests\TestCase;
use DateInterval;
use InvalidArgumentException;

final class DocumentationMonitoringSecurityPerformanceUnitTest extends TestCase
{
    public function test_openapi_generator_exposes_paths() : void
    {
        $document = ApiDocumentation::openApi(routes: [['method' => 'GET', 'path' => '/health']]);

        self::assertArrayHasKey(key: '/health', array: $document['paths']);
    }

    public function test_swagger_ui_points_to_openapi_url() : void
    {
        self::assertStringContainsString(needle: '/openapi.json', haystack: ApiDocumentation::swagger(openApiUrl: '/openapi.json'));
    }

    public function test_monitoring_health_defaults_to_up() : void
    {
        self::assertSame(expected: 'up', actual: Monitoring::health()->status);
    }

    public function test_monitoring_health_degrades_when_check_is_down() : void
    {
        $report = Monitoring::health(checks: ['database' => new HealthCheckResult(status: 'down', error: 'offline')]);

        self::assertSame(expected: 'degraded', actual: $report->status);
    }

    public function test_metrics_registry_counts_events() : void
    {
        Monitoring::metrics()->increment(name: 'unit_event_total');

        self::assertArrayHasKey(key: 'unit_event_total', array: Monitoring::metrics()->snapshot()['counters']);
    }

    public function test_monitoring_dashboard_contains_health_and_metrics() : void
    {
        $dashboard = Monitoring::dashboard();

        self::assertArrayHasKey(key: 'health', array: $dashboard);
        self::assertArrayHasKey(key: 'metrics', array: $dashboard);
    }

    public function test_security_escapes_html_output() : void
    {
        self::assertSame(expected: '&lt;script&gt;', actual: Security::escape(value: '<script>'));
    }

    public function test_security_safe_json_escapes_script_tags() : void
    {
        self::assertStringContainsString(needle: '\\u003C\\/script\\u003E', haystack: Security::safeJson(value: ['x' => '</script>']));
    }

    public function test_security_rejects_unknown_mass_assignment_keys() : void
    {
        $this->expectException(exception: InvalidArgumentException::class);

        Security::fillable(input: ['name' => 'Ada', 'admin' => true], fillable: ['name']);
    }

    public function test_security_allows_fillable_keys() : void
    {
        self::assertSame(expected: ['name' => 'Ada'], actual: Security::fillable(input: ['name' => 'Ada'], fillable: ['name']));
    }

    public function test_security_records_audit_events() : void
    {
        Security::audit(event: 'unit.audit', context: ['token' => 'secret']);
        $events = Security::auditEvents();
        $event  = $events[array_key_last(array: $events)];

        self::assertSame(expected: '[redacted]', actual: $event['context']['token']);
    }

    public function test_security_headers_are_applied_immutably() : void
    {
        $formatter = Security::applySecurityHeaders(responseFormatter: new ResponseFormatter());

        self::assertSame(expected: 'nosniff', actual: $formatter->headers()['X-Content-Type-Options']);
    }

    public function test_signed_urls_verify_before_expiration() : void
    {
        SignedUrlGenerator::configure(secret: 'unit-secret');

        self::assertTrue(condition: Security::verifySignedUrl(url: Security::generateSignedUrl(path: '/download', ttl: new DateInterval(duration: 'PT1M'))));
    }

    public function test_query_cache_remembers_value() : void
    {
        $calls = 0;
        $cache = Performance::queryCache();

        $first  = $cache->remember(key: 'query-cache-unit', query: static function () use (&$calls) : string {
            $calls++;

            return 'value';
        });
        $second = $cache->remember(key: 'query-cache-unit', query: static function () use (&$calls) : string {
            $calls++;

            return 'new';
        });

        self::assertSame(expected: 'value', actual: $first);
        self::assertSame(expected: 'value', actual: $second);
        self::assertSame(expected: 1, actual: $calls);
    }

    public function test_lazy_value_resolves_once() : void
    {
        $calls = 0;
        $lazy  = Performance::lazy(resolver: static function () use (&$calls) : string {
            $calls++;

            return 'loaded';
        });

        $lazy->get();
        $lazy->get();

        self::assertSame(expected: 1, actual: $calls);
    }

    public function test_route_cache_reads_written_routes() : void
    {
        $path  = sys_get_temp_dir() . '/avax-routes-' . uniqid() . '.php';
        $cache = Performance::routes(path: $path);

        $cache->write(routes: [['method' => 'GET', 'path' => '/']]);

        self::assertSame(expected: '/', actual: $cache->read()[0]['path']);
    }

    public function test_config_cache_reads_written_config() : void
    {
        $path  = sys_get_temp_dir() . '/avax-config-' . uniqid() . '.php';
        $cache = Performance::config(path: $path);

        $cache->write(config: ['app' => ['name' => 'Avax']]);

        self::assertSame(expected: 'Avax', actual: $cache->read()['app']['name']);
    }

    public function test_config_cache_clear_removes_file() : void
    {
        $path  = sys_get_temp_dir() . '/avax-config-clear-' . uniqid() . '.php';
        $cache = Performance::config(path: $path);
        $cache->write(config: ['x' => true]);

        $cache->clear();

        self::assertSame(expected: [], actual: $cache->read());
    }
}
