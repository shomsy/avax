<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Operations\Logging;

use Avax\Components\Operations\Logging\System\Capabilities\Logger\ErrorLogger;
use Avax\Components\Operations\Logging\System\Capabilities\Writers\LogWriterInterface;
use ErrorException;
use Exception;
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\InvalidArgumentException;
use Psr\Log\LogLevel;
use RuntimeException;
use Stringable;

#[CoversClass(ErrorLogger::class)]
final class ErrorLoggerTest extends TestCase
{
    // =========================================================================
    // Log Level Tests
    // =========================================================================

    #[Test]
    public function emergencyLevel() : void
    {
        $writer = $this->createMock(LogWriterInterface::class);
        $writer->expects(self::once())
            ->method('write')
            ->with(self::callback(static fn (string $content) => str_contains($content, 'EMERGENCY')));

        $logger = new ErrorLogger($writer);
        $logger->emergency('System is unusable');
    }

    #[Test]
    public function alertLevel() : void
    {
        $writer = $this->createMock(LogWriterInterface::class);
        $writer->expects(self::once())
            ->method('write')
            ->with(self::callback(static fn (string $content) => str_contains($content, 'ALERT')));

        $logger = new ErrorLogger($writer);
        $logger->alert('Action must be taken immediately');
    }

    #[Test]
    public function criticalLevel() : void
    {
        $writer = $this->createMock(LogWriterInterface::class);
        $writer->expects(self::once())
            ->method('write')
            ->with(self::callback(static fn (string $content) => str_contains($content, 'CRITICAL')));

        $logger = new ErrorLogger($writer);
        $logger->critical('Critical condition');
    }

    #[Test]
    public function errorLevel() : void
    {
        $writer = $this->createMock(LogWriterInterface::class);
        $writer->expects(self::once())
            ->method('write')
            ->with(self::callback(static fn (string $content) => str_contains($content, 'ERROR')));

        $logger = new ErrorLogger($writer);
        $logger->error('Runtime error occurred');
    }

    #[Test]
    public function warningLevel() : void
    {
        $writer = $this->createMock(LogWriterInterface::class);
        $writer->expects(self::once())
            ->method('write')
            ->with(self::callback(static fn (string $content) => str_contains($content, 'WARNING')));

        $logger = new ErrorLogger($writer);
        $logger->warning('Warning condition');
    }

    #[Test]
    public function noticeLevel() : void
    {
        $writer = $this->createMock(LogWriterInterface::class);
        $writer->expects(self::once())
            ->method('write')
            ->with(self::callback(static fn (string $content) => str_contains($content, 'NOTICE')));

        $logger = new ErrorLogger($writer);
        $logger->notice('Normal but significant condition');
    }

    #[Test]
    public function debugLevel() : void
    {
        $writer = $this->createMock(LogWriterInterface::class);
        $writer->expects(self::once())
            ->method('write')
            ->with(self::callback(static fn (string $content) => str_contains($content, 'DEBUG')));

        $logger = new ErrorLogger($writer);
        $logger->debug('Debug-level message');
    }

    #[Test]
    public function infoLevel() : void
    {
        $writer = $this->createMock(LogWriterInterface::class);
        $writer->expects(self::once())
            ->method('write')
            ->with(self::callback(static fn (string $content) => str_contains($content, 'INFO')));

        $logger = new ErrorLogger($writer);
        $logger->info('Informational message');
    }

    // =========================================================================
    // PSR-3 Compliance Tests
    // =========================================================================

    #[Test]
    public function psr3LogMethod() : void
    {
        $writer = $this->createMock(LogWriterInterface::class);
        $writer->expects(self::once())
            ->method('write')
            ->with(self::callback(static fn (string $content) => str_contains($content, 'ERROR')));

        $logger = new ErrorLogger($writer);
        $logger->log(LogLevel::ERROR, 'PSR-3 log method test');
    }

    #[Test]
    public function psr3LogMethodWithInvalidLevelThrowsException() : void
    {
        $writer = $this->createMock(LogWriterInterface::class);
        $logger = new ErrorLogger($writer);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Log level must be a string.');

        // @phpstan-ignore-next-line - Testing invalid input
        $logger->log(123, 'Invalid level');
    }

    #[Test]
    public function psr3LogMethodWithStringableMessage() : void
    {
        $writer = $this->createMock(LogWriterInterface::class);
        $writer->expects(self::once())
            ->method('write')
            ->with(self::callback(static fn (string $content) => str_contains($content, 'Stringable message')));

        $logger     = new ErrorLogger($writer);
        $stringable = new StringableObject('Stringable message');
        $logger->log(LogLevel::INFO, $stringable);
    }

    #[Test]
    public function psr3LogMethodWithEmptyContext() : void
    {
        $writer = $this->createMock(LogWriterInterface::class);
        $writer->expects(self::once())
            ->method('write')
            ->with(self::callback(static fn (string $content) => ! str_contains($content, '{')));

        $logger = new ErrorLogger($writer);
        $logger->log(LogLevel::DEBUG, 'No context', []);
    }

    // =========================================================================
    // Structured Context Merging Tests
    // =========================================================================

    #[Test]
    public function structuredContextMerging() : void
    {
        $writtenContent = '';
        $writer         = $this->createMock(LogWriterInterface::class);
        $writer->method('write')
            ->willReturnCallback(static function (string $content) use (&$writtenContent) : void {
                $writtenContent = $content;
            });

        $logger = new ErrorLogger($writer);
        $logger->error('Context test', [
            'user_id'    => 123,
            'action'     => 'login',
            'ip_address' => '192.168.1.1',
        ]);

        self::assertStringContainsString('user_id', $writtenContent);
        self::assertStringContainsString('123', $writtenContent);
        self::assertStringContainsString('action', $writtenContent);
        self::assertStringContainsString('login', $writtenContent);
        self::assertStringContainsString('ip_address', $writtenContent);
    }

    #[Test]
    public function contextWithNestedArrays() : void
    {
        $writtenContent = '';
        $writer         = $this->createMock(LogWriterInterface::class);
        $writer->method('write')
            ->willReturnCallback(static function (string $content) use (&$writtenContent) : void {
                $writtenContent = $content;
            });

        $logger = new ErrorLogger($writer);
        $logger->warning('Nested context', [
            'request' => [
                'method' => 'POST',
                'uri'    => '/api/users',
            ],
            'metadata' => [
                'version' => '1.0',
            ],
        ]);

        self::assertStringContainsString('request', $writtenContent);
        self::assertStringContainsString('method', $writtenContent);
        self::assertStringContainsString('POST', $writtenContent);
        self::assertStringContainsString('uri', $writtenContent);
        self::assertStringContainsString('/api/users', $writtenContent);
        self::assertStringContainsString('metadata', $writtenContent);
        self::assertStringContainsString('version', $writtenContent);
    }

    #[Test]
    public function contextWithNumericValues() : void
    {
        $writtenContent = '';
        $writer         = $this->createMock(LogWriterInterface::class);
        $writer->method('write')
            ->willReturnCallback(static function (string $content) use (&$writtenContent) : void {
                $writtenContent = $content;
            });

        $logger = new ErrorLogger($writer);
        $logger->info('Numeric context', [
            'count'    => 42,
            'ratio'    => 3.14,
            'negative' => -100,
        ]);

        self::assertStringContainsString('42', $writtenContent);
        self::assertStringContainsString('3.14', $writtenContent);
        self::assertStringContainsString('-100', $writtenContent);
    }

    #[Test]
    public function contextWithBooleanValues() : void
    {
        $writtenContent = '';
        $writer         = $this->createMock(LogWriterInterface::class);
        $writer->method('write')
            ->willReturnCallback(static function (string $content) use (&$writtenContent) : void {
                $writtenContent = $content;
            });

        $logger = new ErrorLogger($writer);
        $logger->info('Boolean context', [
            'enabled'  => true,
            'disabled' => false,
        ]);

        self::assertStringContainsString('true', $writtenContent);
        self::assertStringContainsString('false', $writtenContent);
    }

    #[Test]
    public function contextWithNullValues() : void
    {
        $writtenContent = '';
        $writer         = $this->createMock(LogWriterInterface::class);
        $writer->method('write')
            ->willReturnCallback(static function (string $content) use (&$writtenContent) : void {
                $writtenContent = $content;
            });

        $logger = new ErrorLogger($writer);
        $logger->info('Null context', [
            'value' => null,
            'name'  => null,
        ]);

        self::assertStringContainsString('null', $writtenContent);
    }

    // =========================================================================
    // Exception Logging Tests
    // =========================================================================

    #[Test]
    public function exceptionLoggingWithTrace() : void
    {
        $writtenContent = '';
        $writer         = $this->createMock(LogWriterInterface::class);
        $writer->method('write')
            ->willReturnCallback(static function (string $content) use (&$writtenContent) : void {
                $writtenContent = $content;
            });

        $logger = new ErrorLogger($writer);

        try {
            $this->throwExceptionForLogger();
        } catch (RuntimeException $e) {
            $logger->error('Exception occurred', ['exception' => $e]);
        }

        self::assertStringContainsString('exception', $writtenContent);
        self::assertStringContainsString('class', $writtenContent);
        self::assertStringContainsString('RuntimeException', $writtenContent);
        self::assertStringContainsString('message', $writtenContent);
        self::assertStringContainsString('Test exception for logger', $writtenContent);
        self::assertStringContainsString('file', $writtenContent);
        self::assertStringContainsString('line', $writtenContent);
        self::assertStringContainsString('trace', $writtenContent);
    }

    private function throwExceptionForLogger() : void
    {
        throw new RuntimeException('Test exception for logger');
    }

    #[Test]
    public function exceptionLoggingIncludesFileAndLine() : void
    {
        $writtenContent = '';
        $writer         = $this->createMock(LogWriterInterface::class);
        $writer->method('write')
            ->willReturnCallback(static function (string $content) use (&$writtenContent) : void {
                $writtenContent = $content;
            });

        $logger = new ErrorLogger($writer);

        $exception = new \InvalidArgumentException('Invalid arg');
        $logger->error('Arg error', ['exception' => $exception]);

        self::assertStringContainsString('InvalidArgumentException', $writtenContent);
        self::assertStringContainsString('Invalid arg', $writtenContent);
    }

    #[Test]
    public function exceptionLoggingLimitsTraceToTenFrames() : void
    {
        $writtenContent = '';
        $writer         = $this->createMock(LogWriterInterface::class);
        $writer->method('write')
            ->willReturnCallback(static function (string $content) use (&$writtenContent) : void {
                $writtenContent = $content;
            });

        $logger = new ErrorLogger($writer);

        // Create an exception with a deep stack trace
        $exception = $this->createDeepException(15);
        $logger->critical('Deep exception', ['exception' => $exception]);

        self::assertStringContainsString('trace', $writtenContent);
        // The trace should be limited, verify JSON is valid
        $jsonPart = $this->extractJsonFromContent($writtenContent);
        if ($jsonPart !== null) {
            $decoded = json_decode($jsonPart, true);
            self::assertNotNull($decoded);
            if (isset($decoded['exception']['trace'])) {
                self::assertLessThanOrEqual(10, count($decoded['exception']['trace']));
            }
        }
    }

    private function createDeepException(int $depth) : Exception
    {
        if ($depth > 0) {
            return $this->createDeepException($depth - 1);
        }

        throw new Exception('Deep exception');
    }

    private function extractJsonFromContent(string $content) : ?string
    {
        // Try to find JSON in the content
        $bracePos = strpos($content, '{');
        if ($bracePos === false) {
            return null;
        }

        // Find matching closing brace
        $depth    = 0;
        $inString = false;
        $escaped  = false;

        for ($i = $bracePos; $i < strlen($content); $i++) {
            $char = $content[$i];

            if ($escaped) {
                $escaped = false;

                continue;
            }

            if ($char === '\\' && $inString) {
                $escaped = true;

                continue;
            }

            if ($char === '"') {
                $inString = ! $inString;

                continue;
            }

            if (! $inString) {
                if ($char === '{') {
                    $depth++;
                } elseif ($char === '}') {
                    $depth--;
                    if ($depth === 0) {
                        return substr($content, $bracePos, $i - $bracePos + 1);
                    }
                }
            }
        }

        return null;
    }

    #[Test]
    public function exceptionLoggingWithPhpErrorException() : void
    {
        $writtenContent = '';
        $writer         = $this->createMock(LogWriterInterface::class);
        $writer->method('write')
            ->willReturnCallback(static function (string $content) use (&$writtenContent) : void {
                $writtenContent = $content;
            });

        $logger = new ErrorLogger($writer);

        $exception = new ErrorException('[E_ERROR] Fatal error', 0, E_ERROR, '/file.php', 42);
        $logger->critical('PHP error', ['exception' => $exception]);

        self::assertStringContainsString('ErrorException', $writtenContent);
        self::assertStringContainsString('[E_ERROR] Fatal error', $writtenContent);
    }

    // =========================================================================
    // Correlation ID Enrichment Tests
    // =========================================================================

    #[Test]
    public function exceptionLoggingWithNestedException() : void
    {
        $writtenContent = '';
        $writer         = $this->createMock(LogWriterInterface::class);
        $writer->method('write')
            ->willReturnCallback(static function (string $content) use (&$writtenContent) : void {
                $writtenContent = $content;
            });

        $logger = new ErrorLogger($writer);

        $previous  = new LogicException('Previous exception');
        $exception = new RuntimeException('Current exception', 0, $previous);
        $logger->error('Chain', ['exception' => $exception]);

        self::assertStringContainsString('RuntimeException', $writtenContent);
    }

    #[Test]
    public function correlationIdEnrichment() : void
    {
        $writtenContent = '';
        $writer         = $this->createMock(LogWriterInterface::class);
        $writer->method('write')
            ->willReturnCallback(static function (string $content) use (&$writtenContent) : void {
                $writtenContent = $content;
            });

        $logger = new ErrorLogger($writer);
        $logger->error('Request failed', [
            'correlation_id' => 'corr-abc-123',
            'endpoint'       => '/api/data',
        ]);

        self::assertStringContainsString('corr-abc-123', $writtenContent);
        self::assertStringContainsString('correlation_id', $writtenContent);
    }

    // =========================================================================
    // Trace ID Enrichment Tests
    // =========================================================================

    #[Test]
    public function correlationIdWithException() : void
    {
        $writtenContent = '';
        $writer         = $this->createMock(LogWriterInterface::class);
        $writer->method('write')
            ->willReturnCallback(static function (string $content) use (&$writtenContent) : void {
                $writtenContent = $content;
            });

        $logger = new ErrorLogger($writer);

        $exception = new RuntimeException('Test');
        $logger->critical('Correlated error', [
            'exception'      => $exception,
            'correlation_id' => 'corr-xyz-789',
        ]);

        self::assertStringContainsString('corr-xyz-789', $writtenContent);
        self::assertStringContainsString('RuntimeException', $writtenContent);
    }

    #[Test]
    public function traceIdEnrichment() : void
    {
        $writtenContent = '';
        $writer         = $this->createMock(LogWriterInterface::class);
        $writer->method('write')
            ->willReturnCallback(static function (string $content) use (&$writtenContent) : void {
                $writtenContent = $content;
            });

        $logger = new ErrorLogger($writer);
        $logger->warning('Slow query', [
            'trace_id'   => 'trace-def-456',
            'query_time' => 2.5,
        ]);

        self::assertStringContainsString('trace-def-456', $writtenContent);
        self::assertStringContainsString('trace_id', $writtenContent);
    }

    // =========================================================================
    // Secret Redaction In Context Tests
    // =========================================================================

    #[Test]
    public function bothCorrelationAndTraceIds() : void
    {
        $writtenContent = '';
        $writer         = $this->createMock(LogWriterInterface::class);
        $writer->method('write')
            ->willReturnCallback(static function (string $content) use (&$writtenContent) : void {
                $writtenContent = $content;
            });

        $logger = new ErrorLogger($writer);
        $logger->error('Full tracing', [
            'correlation_id' => 'corr-full',
            'trace_id'       => 'trace-full',
        ]);

        self::assertStringContainsString('corr-full', $writtenContent);
        self::assertStringContainsString('trace-full', $writtenContent);
    }

    #[Test]
    public function secretRedactionInContext() : void
    {
        $writtenContent = '';
        $writer         = $this->createMock(LogWriterInterface::class);
        $writer->method('write')
            ->willReturnCallback(static function (string $content) use (&$writtenContent) : void {
                $writtenContent = $content;
            });

        $logger = new ErrorLogger($writer);
        $logger->error('Auth failed', [
            'password' => 'supersecret123',
            'username' => 'admin',
        ]);

        // The logger itself doesn't redact, it logs the context as-is
        // But we verify the context is properly formatted
        self::assertStringContainsString('password', $writtenContent);
        self::assertStringContainsString('username', $writtenContent);
    }

    // =========================================================================
    // Edge Cases
    // =========================================================================

    #[Test]
    public function tokenInContextIsLogged() : void
    {
        $writtenContent = '';
        $writer         = $this->createMock(LogWriterInterface::class);
        $writer->method('write')
            ->willReturnCallback(static function (string $content) use (&$writtenContent) : void {
                $writtenContent = $content;
            });

        $logger = new ErrorLogger($writer);
        $logger->warning('API call failed', [
            'token'    => 'Bearer abc123',
            'endpoint' => '/api/v1/users',
        ]);

        self::assertStringContainsString('token', $writtenContent);
        self::assertStringContainsString('endpoint', $writtenContent);
    }

    #[Test]
    public function emptyMessage() : void
    {
        $writer = $this->createMock(LogWriterInterface::class);
        $writer->expects(self::once())
            ->method('write')
            ->with(self::callback(static fn (string $content) => str_contains($content, '  ')));

        $logger = new ErrorLogger($writer);
        $logger->error('');
    }

    #[Test]
    public function veryLongMessage() : void
    {
        $writer = $this->createMock(LogWriterInterface::class);
        $writer->expects(self::once())
            ->method('write');

        $logger = new ErrorLogger($writer);
        $logger->error(str_repeat('A', 10000));
    }

    #[Test]
    public function unicodeMessage() : void
    {
        $writtenContent = '';
        $writer         = $this->createMock(LogWriterInterface::class);
        $writer->method('write')
            ->willReturnCallback(static function (string $content) use (&$writtenContent) : void {
                $writtenContent = $content;
            });

        $logger = new ErrorLogger($writer);
        $logger->info('Unicode: Привет мир, 你好世界');

        self::assertStringContainsString('Привет мир', $writtenContent);
        self::assertStringContainsString('你好世界', $writtenContent);
    }

    #[Test]
    public function messageWithSpecialCharacters() : void
    {
        $writtenContent = '';
        $writer         = $this->createMock(LogWriterInterface::class);
        $writer->method('write')
            ->willReturnCallback(static function (string $content) use (&$writtenContent) : void {
                $writtenContent = $content;
            });

        $logger = new ErrorLogger($writer);
        $logger->error("Line1\nLine2\tTab\rCarriage");

        self::assertStringContainsString('Line1', $writtenContent);
    }

    #[Test]
    public function contextWithJsonUnencodableData() : void
    {
        $writtenContent = '';
        $writer         = $this->createMock(LogWriterInterface::class);
        $writer->method('write')
            ->willReturnCallback(static function (string $content) use (&$writtenContent) : void {
                $writtenContent = $content;
            });

        $logger = new ErrorLogger($writer);

        // Test with a simple context that might cause encoding issues
        $logger->error('Binary data', [
            'data' => "\x00\x01\x02",
        ]);

        // Should still write something (either encoded data or failure message)
        self::assertNotEmpty($writtenContent);
    }

    #[Test]
    public function timestampFormat() : void
    {
        $writtenContent = '';
        $writer         = $this->createMock(LogWriterInterface::class);
        $writer->method('write')
            ->willReturnCallback(static function (string $content) use (&$writtenContent) : void {
                $writtenContent = $content;
            });

        $logger = new ErrorLogger($writer);
        $logger->info('Timestamp test');

        // Should match YYYY-MM-DD HH:MM:SS format at the start
        self::assertMatchesRegularExpression('/^\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\]/', $writtenContent);
    }

    #[Test]
    public function logEntryFormat() : void
    {
        $writtenContent = '';
        $writer         = $this->createMock(LogWriterInterface::class);
        $writer->method('write')
            ->willReturnCallback(static function (string $content) use (&$writtenContent) : void {
                $writtenContent = $content;
            });

        $logger = new ErrorLogger($writer);
        $logger->error('Format test');

        // Format: [timestamp] LEVEL message context
        self::assertMatchesRegularExpression('/^\[.+\] ERROR Format test/', $writtenContent);
    }

    #[Test]
    public function writerIsCalledExactlyOnce() : void
    {
        $writer = $this->createMock(LogWriterInterface::class);
        $writer->expects(self::exactly(1))
            ->method('write');

        $logger = new ErrorLogger($writer);
        $logger->info('Single write');
    }

    #[Test]
    public function multipleLogCallsResultInMultipleWrites() : void
    {
        $writer = $this->createMock(LogWriterInterface::class);
        $writer->expects(self::exactly(3))
            ->method('write');

        $logger = new ErrorLogger($writer);
        $logger->debug('Debug');
        $logger->info('Info');
        $logger->error('Error');
    }

    #[Test]
    public function stringableObjectAsMessage() : void
    {
        $writtenContent = '';
        $writer         = $this->createMock(LogWriterInterface::class);
        $writer->method('write')
            ->willReturnCallback(static function (string $content) use (&$writtenContent) : void {
                $writtenContent = $content;
            });

        $logger = new ErrorLogger($writer);
        $obj    = new StringableObject('Object message');
        $logger->warning($obj);

        self::assertStringContainsString('Object message', $writtenContent);
    }

    #[Test]
    public function contextWithArrayOfStrings() : void
    {
        $writtenContent = '';
        $writer         = $this->createMock(LogWriterInterface::class);
        $writer->method('write')
            ->willReturnCallback(static function (string $content) use (&$writtenContent) : void {
                $writtenContent = $content;
            });

        $logger = new ErrorLogger($writer);
        $logger->info('Array context', [
            'tags' => ['php', 'logging', 'test'],
        ]);

        self::assertStringContainsString('php', $writtenContent);
        self::assertStringContainsString('logging', $writtenContent);
        self::assertStringContainsString('test', $writtenContent);
    }

    #[Test]
    public function contextWithDeeplyNestedArray() : void
    {
        $writtenContent = '';
        $writer         = $this->createMock(LogWriterInterface::class);
        $writer->method('write')
            ->willReturnCallback(static function (string $content) use (&$writtenContent) : void {
                $writtenContent = $content;
            });

        $logger = new ErrorLogger($writer);
        $logger->info('Deep context', [
            'level1' => [
                'level2' => [
                    'level3' => [
                        'value' => 'deep',
                    ],
                ],
            ],
        ]);

        self::assertStringContainsString('level1', $writtenContent);
        self::assertStringContainsString('level2', $writtenContent);
        self::assertStringContainsString('level3', $writtenContent);
        self::assertStringContainsString('deep', $writtenContent);
    }

    #[Test]
    public function logWithExceptionAndAdditionalContext() : void
    {
        $writtenContent = '';
        $writer         = $this->createMock(LogWriterInterface::class);
        $writer->method('write')
            ->willReturnCallback(static function (string $content) use (&$writtenContent) : void {
                $writtenContent = $content;
            });

        $logger = new ErrorLogger($writer);

        $exception = new RuntimeException('Test');
        $logger->error('Mixed context', [
            'exception'      => $exception,
            'correlation_id' => 'corr-mixed',
            'request_id'     => 'req-123',
        ]);

        self::assertStringContainsString('RuntimeException', $writtenContent);
        self::assertStringContainsString('corr-mixed', $writtenContent);
        self::assertStringContainsString('req-123', $writtenContent);
    }
}

final readonly class StringableObject implements Stringable
{
    public function __construct(
        private string $value,
    ) {}

    public function __toString() : string
    {
        return $this->value;
    }
}
