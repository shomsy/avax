<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Framework\V4RuntimeApp;

use Avax\Components\HTTP\Router\System\Foundation\Exceptions\MethodNotAllowedException;
use Avax\Components\HTTP\Router\System\Foundation\Exceptions\RouteNotFoundException;
use Avax\Components\HTTP\SecureRequest\System\Foundation\Failure\SecureRequestAuthorizationFailed;
use Avax\Components\HTTP\SecureRequest\System\Foundation\Failure\SecureRequestValidationFailed;
use Avax\Framework\System\Capabilities\FailureBoundary\Capabilities\ClassifyApplicationException\ClassifyApplicationException;
use Avax\Framework\System\Foundation\Failure\FrameworkMisconfigured;
use Exception;
use InvalidArgumentException;
use RuntimeException;
use Avax\Tests\TestCase;

/**
 * @covers \Avax\Framework\System\Capabilities\FailureBoundary\Capabilities\ClassifyApplicationException\ClassifyApplicationException
 */
final class ClassifyApplicationExceptionTest extends TestCase
{
    private ClassifyApplicationException $classifier;

    protected function setUp(): void
    {
        parent::setUp();
        $this->classifier = new ClassifyApplicationException();
    }

    public function testClassifyValidationException(): void
    {
        $exception = new SecureRequestValidationFailed('Invalid email');
        $result = $this->classifier->classify($exception);

        self::assertSame('validation', $result['category']);
        self::assertSame(422, $result['statusCode']);
        self::assertSame('Validation failed', $result['safeMessage']);
    }

    public function testClassifyAuthorizationException(): void
    {
        $exception = new SecureRequestAuthorizationFailed('Not admin');
        $result = $this->classifier->classify($exception);

        self::assertSame('authorization', $result['category']);
        self::assertSame(403, $result['statusCode']);
    }

    public function testClassifyRouteNotFoundException(): void
    {
        $exception = new RouteNotFoundException('/missing');
        $result = $this->classifier->classify($exception);

        self::assertSame('not_found', $result['category']);
        self::assertSame(404, $result['statusCode']);
    }

    public function testClassifyMethodNotAllowedException(): void
    {
        $exception = new MethodNotAllowedException();
        $result = $this->classifier->classify($exception);

        self::assertSame('method_not_allowed', $result['category']);
        self::assertSame(405, $result['statusCode']);
    }

    public function testClassifyGenericException(): void
    {
        $exception = new RuntimeException('Something broke');
        $result = $this->classifier->classify($exception);

        self::assertSame('system', $result['category']);
        self::assertSame(500, $result['statusCode']);
    }

    public function testClassifyInvalidArgumentException(): void
    {
        $exception = new InvalidArgumentException('Bad argument');
        $result = $this->classifier->classify($exception);

        self::assertSame('system', $result['category']);
        self::assertSame(500, $result['statusCode']);
    }

    public function testClassifyFrameworkMisconfigured(): void
    {
        $exception = new FrameworkMisconfigured('Path not found');
        $result = $this->classifier->classify($exception);

        self::assertSame('system', $result['category']);
        self::assertSame(500, $result['statusCode']);
    }

    public function testSafeMessageDoesNotLeakSensitiveData(): void
    {
        $exception = new Exception('Database password: secret123');
        $result = $this->classifier->classify($exception);

        self::assertNotEmpty($result['safeMessage']);
        self::assertStringNotContainsString('secret123', $result['safeMessage']);
    }
}
