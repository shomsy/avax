# Dependency Boundary

## Assembled Dependencies

`BuildRunApplication` assembles:

- `RouteFacadeContainer`
- `ResolveCallable`
- `ControllerResolver`
- `ArgumentResolver`
- `SecureRequestInputBuilder`
- `ReadIncomingHttpRequest`
- `MatchHttpRoute`
- `MatchRoute`
- `RunApplication`

## Injected Dependencies

`App` now receives:

- `RuntimeInterface`
- `ResetApplicationState`
- `CreateHttpResponse`
- `CreateRequestFromGlobals`
- `RunApplication`

`RunApplication` receives all runtime dispatch collaborators through its constructor.

## Allowed Direction

Configuration -> runtime flow -> execution.

## Forbidden Direction Removed

Runtime flow -> Configuration-style object graph assembly.

## Residual Boundary Findings

Direct-instantiation gate still reports in-scope residual findings in `App.php` and `BootDsl.php`. They are not fixed in Slice A to avoid broad cleanup.
