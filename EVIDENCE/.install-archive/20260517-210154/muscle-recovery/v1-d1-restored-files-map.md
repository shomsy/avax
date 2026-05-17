# V1-D1 Restored Files Map

## HTTP/Router (34 files)

```
components/HTTP/Router/System/
├── PublicSurface/
│   ├── Router.php ✓
│   ├── RouterInterface.php ✓
│   ├── RouterRuntimeInterface.php ✓
│   └── shortcuts.php ✓
├── Flows/
│   ├── RegisterRoute/
│   │   ├── RegisterRoute.php ✓
│   │   ├── NormalizeRouteDefinition.php ✓
│   │   └── RouteRegistrationFailed.php ✓
│   ├── RegisterRoutes/
│   │   ├── Definitions/
│   │   │   ├── RouteRegistry.php ✓
│   │   │   ├── RouteBuilder.php ✓
│   │   │   ├── RoutePathValidator.php ✓
│   │   │   └── RouterRegistrar.php ✓
│   │   ├── Files/
│   │   │   └── Registrar.php ✓
│   │   ├── Groups/
│   │   │   └── RouteGroupContext.php ✓
│   │   └── Attributes/
│   │       └── AttributeRouteRegistrar.php ✓
│   ├── MatchRoute/
│   │   ├── MatchRoute.php ✓
│   │   ├── MatchStaticRoute.php ✓
│   │   ├── MatchDynamicRoute.php ✓
│   │   └── RouteNotFound.php ✓
│   ├── DispatchRoute/
│   │   ├── DispatchRoute.php ✓
│   │   ├── InvokeRouteAction.php ✓
│   │   ├── ResolveRouteAction.php ✓
│   │   └── RouteDispatchFailed.php ✓
│   └── GenerateUrl/
│       └── GenerateUrl.php ✓
├── Capabilities/
│   ├── Routes/
│   ├── Matching/
│   ├── Parameters/
│   └── Groups/
├── Configuration/
│   └── RouterBootstrapper.php ✓
└── Foundation/
    ├── Failure/
    │   └── RouterFailure.php
    └── Values/
```

## HTTP/Middleware (12 files)

```
components/HTTP/Middleware/System/
├── PublicSurface/
│   ├── Middleware.php ✓
│   └── MiddlewareInterface.php ✓
├── Flows/
│   └── RunMiddlewarePipeline/
│       └── RunMiddlewarePipeline.php ✓
├── Capabilities/
│   ├── Pipeline/
│   │   └── MiddlewarePipeline.php ✓
│   ├── Stack/
│   │   └── MiddlewareStack.php ✓
│   └── CallNext/
│       └── CallNextMiddleware.php ✓
├── Configuration/
└── Foundation/
    └── Failure/
        └── MiddlewareFailure.php
```

---

## Restoration Complete

Both components are already restored. No new code written.
Backup code from avax-backup.txt was used as source material.