# TODO-006 Slice C High-Level Design

## Problem

`App` is PublicSurface but still constructs internal route/request/scope objects directly during route registration and request handling.

## Target Design

`App` receives these collaborators through its constructor:

- route registrar: stores route definitions and exports `RegisteredHttpRoutes`
- open request scope flow
- close request scope flow
- runtime-request conversion flow

Configuration/composition paths build these collaborators when they already have the `Runtime` instance.

## Boundary

PublicSurface:

- fluent route API
- request/run API
- delegates to injected collaborators

Flows:

- route registrar owns route-definition construction
- runtime-request converter owns request object conversion
- scope flows own open/close behavior

Configuration/composition:

- assembles the collaborators with the booted runtime

## Compatibility

No public `App` method signatures change.

Existing route behavior, request handling, exception handling, reset behavior, and kernel adapter behavior must remain stable.
