# HTTP Integration

## Middleware

The `HttpFailureBoundaryMiddleware` wraps the HTTP request pipeline in a failure boundary.

```php
use Avax\Framework\System\Capabilities\FailureBoundary\Configuration\BuildFailureBoundary;
use Avax\Framework\System\Capabilities\FailureBoundary\Integration\HttpFailureBoundaryMiddleware;

$boundary = (new BuildFailureBoundary())->build();
$middleware = new HttpFailureBoundaryMiddleware($boundary);
```

## Integration into AppKernel

Add the middleware early in the AppKernel middleware stack so it catches all downstream failures:

```php
// In your AppKernel bootstrap:
$failureBoundary = (new BuildFailureBoundary())->build();
$failureMiddleware = new HttpFailureBoundaryMiddleware($failureBoundary);

$kernel = new AppKernel(
    router: $router,
    middleware: [
        $failureMiddleware,  // First — catches everything downstream
        ...$otherMiddleware,
    ],
);
```

## How It Works

1. Middleware receives the request
2. Creates a `FailureContext::forHttp($request)`
3. Calls `RunProtectedAction::run()` wrapping `$next($request)`
4. On success: returns the response
5. On failure:
   - Resolves compiled failure policy for the target
   - Classifies the failure
   - Reports to configured channel
   - Executes the selected decision (map to response, retry, fallback, etc.)
6. Cleanup always runs (finally block)

## Example Controller

```php
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\Attributes\OnFailure;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\Attributes\ReportFailure;

final class UserController
{
    #[OnFailure(ValidationFailed::class, respondWith: 422)]
    #[OnFailure(AuthenticationFailed::class, respondWith: 401)]
    #[ReportFailure(channel: 'users')]
    public function register(RegisterUserData $data): JsonResponse
    {
        return $this->users->register($data);
    }
}
```

The middleware reads the compiled policy from cache and applies the failure rules automatically.

## Static Facade

For quick usage without middleware:

```php
use Avax\Framework\System\Capabilities\FailureBoundary\PublicSurface\FailureBoundary;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\FailureContext;

$response = FailureBoundary::forHttp(
    action: fn () => $this->users->register($data),
    request: $request,
    targetClass: UserController::class,
    targetMethod: 'register',
);
```
