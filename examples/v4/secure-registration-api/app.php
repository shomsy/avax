<?php

declare(strict_types=1);

/**
 * Secure Registration API reference application.
 *
 * Proves: SecureRequest, DataTransfer, validation, policy, error handling,
 * and FailureBoundary attribute adoption (OnFailure + ReportFailure).
 *
 * Run: php examples/v4/secure-registration-api/app.php
 */

require __DIR__ . '/../../vendor/autoload.php';

use Avax\Examples\SecureRegistrationApi\RegistrationController;
use Avax\Framework\System\Capabilities\FailureBoundary\Configuration\BuildFailureBoundary;
use Avax\Framework\System\Capabilities\FailureBoundary\Integration\HttpFailureBoundaryMiddleware;
use Avax\Framework\System\PublicSurface\Avax;

$app = Avax::create();

// Register FailureBoundary middleware for this reference app.
// This wires the declarative failure boundary into the request pipeline.
$app->use(static function ($request, callable $next) {
    $builder = new BuildFailureBoundary();
    $middleware = new HttpFailureBoundaryMiddleware($builder->build());

    return $middleware->handle($request, $next);
});

$controller = new RegistrationController();

$app->post('/register', static function ($request) use ($controller) {
    $body = json_decode($request->getBody()->getContents(), true) ?? [];

    return $controller->register($body);
});

$app->post('/verify/{provider}', static function (string $provider) use ($controller) {
    return $controller->verifyIdentity($provider);
});

$app->get('/health', static fn () => ['status' => 'ok']);

$GLOBALS['_APP_RUN'] = false;

if (php_sapi_name() === 'cli' && $GLOBALS['_APP_RUN'] ?? true) {
    $app->run();
}
