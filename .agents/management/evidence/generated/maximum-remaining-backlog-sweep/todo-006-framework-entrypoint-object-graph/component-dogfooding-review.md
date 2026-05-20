# Component Dogfooding Review

## Components Used

- Application Container: `ResolveCallable`
- HTTP Dispatcher: `ControllerResolver`, `ArgumentResolver`
- HTTP Router: `MatchRoute`, `RouteDefinition`
- HTTP SecureRequest: `SecureRequestInputBuilder`
- HTTP Response: `CreateHttpResponse`
- Operations Observability: `MetricsCollector`
- Framework runtime flows: `ReadIncomingHttpRequest`, `MatchHttpRoute`, `RunApplication`

## Components Bypassed

None newly bypassed by Slice A.

The default dispatch pipeline continues to dogfood the existing AvaX router, dispatcher, secure request, response, and observability components.

## Raw Primitives Used

No new raw PHP primitive replaces an AvaX component.

## Dependency Direction

Configuration assembles concrete component collaborators. Runtime flow executes with injected collaborators.

## Classification

DOGFOODS_EXISTING_COMPONENTS.

## Accepted Yellow

`RouteFacadeContainer` remains a minimal PSR-11 container for the zero-configuration dispatch path. This is inherited behavior, not introduced by Slice A.
