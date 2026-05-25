# Identity Architecture

The component is intentionally split around runtime graphs instead of one large constructor or one central all-knowing class.

`AuthenticationGraph` owns password authentication and authenticated identity resolution.

`AuthorizationGraph` owns permission decisions.

`SessionGraph` owns session creation and storage.

`TokenGraph` owns signed access token issuing and verification.

`ExternalIdentityGraph` owns external-provider identity linking.

`TenancyGraph` owns tenant context resolution.

`IdentityRuntimeGraph` composes these graphs and exposes the cohesive runtime boundary used by `PublicSurface\Identity`.

## Key rules

No service locator.
No `app()` or `resolve()` helper.
No static singleton runtime state.
No hidden runtime construction in flows or public surface.
No cross-component internal reach-through.
Configuration graph classes may construct dependencies because they are explicit composition roots.

## Runtime safety

Mutable in-memory capabilities implement `Foundation\State\ResettableIdentityState` and are collected by `ResetIdentityRuntime`.
This is worker-runtime friendly: RoadRunner, Swoole, Workerman, FrankenPHP and other long-lived runtimes can call one reset flow between requests.
