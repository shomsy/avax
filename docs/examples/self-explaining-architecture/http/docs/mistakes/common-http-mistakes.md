# Common HTTP Mistakes

## Mistake 1: Business Logic in Controllers/Handlers

**Wrong:**
```php
class UserController {
    public function getUser($id) {
        $user = $this->database->query("SELECT * FROM users WHERE id = $id");
        $this->sendEmail($user->email, "Profile viewed");
        return json($user);
    }
}
```

**Right:**
```php
#[Route('/users/{id}', method: 'GET')]
class GetUserProfile extends Flow {
    public function execute(Request $request, Response $response): void {
        // Flow orchestrates. Capabilities own behavior.
        $user = $this->userCapability->findById($request->param('id'));
        $this->eventCapability->emit(new UserProfileViewed($user->id));
        $response->json($user->toArray());
    }
}
```

**Why it matters:** Controllers that own business logic become untestable god-objects. Flows orchestrate, Capabilities own behavior.

---

## Mistake 2: No Middleware for Security

**Wrong:**
```php
// Checking auth inside every Flow manually
class GetUserProfile extends Flow {
    public function execute(Request $request, Response $response): void {
        if (!$request->hasValidToken()) {
            return $response->status(401);
        }
        // ... actual logic
    }
}
```

**Right:**
```php
// Auth is a middleware that runs BEFORE the Flow
#[Middleware(AuthMiddleware::class)]
#[Route('/users/{id}', method: 'GET')]
class GetUserProfile extends Flow {
    public function execute(Request $request, Response $response): void {
        // Flow can assume user is authenticated
        $user = $request->authenticatedUser();
        // ...
    }
}
```

**Why it matters:** Security checks scattered across Flows are easy to forget. Middleware enforces security at the boundary.

---

## Mistake 3: Exposing Internal Details in Error Responses

**Wrong:**
```php
catch (DatabaseException $e) {
    return response(500, "Database error: " . $e->getMessage() . " in " . $e->getFile());
}
```

**Right:**
```php
catch (DatabaseException $e) {
    $this->logger->error("Database error in GetUserProfile", ['exception' => $e]);
    return $response->status(500)->json(['error' => 'Internal server error']);
}
```

**Why it matters:** Error messages with stack traces help attackers understand your system. Log the detail, return a generic message.

---

## Mistake 4: Not Validating Input at the HTTP Boundary

**Wrong:**
```php
$id = $request->param('id');
$user = $this->userCapability->findById($id); // What if $id is "DROP TABLE users"?
```

**Right:**
```php
$id = $request->validatedInt('id'); // Fails with 400 if not a valid integer
$user = $this->userCapability->findById($id);
```

**Why it matters:** Input validation at the HTTP boundary prevents injection attacks before they reach business logic.

---

## Mistake 5: Mutable Static State in Long-Lived Workers

**Wrong:**
```php
class RouteCache {
    private static array $routes = []; // Stale routes after deployment!

    public static function get(string $path): ?Flow {
        if (empty(self::$routes)) {
            self::$routes = self::buildRoutes(); // Only runs once per worker
        }
        return self::$routes[$path] ?? null;
    }
}
```

**Right:**
```php
class RouteCache implements ResettableState {
    private array $routes = [];

    public function reset(): void {
        $this->routes = []; // Clears on each request in long-lived workers
    }
}
```

**Why it matters:** In FrankenPHP/RoadRunner/Swoole, workers live across many requests. Static state from request 1 leaks into request 2.
