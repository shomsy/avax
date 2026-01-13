# Presentation/HTTP/routes

## What This Folder Represents and Why It Exists

Technical: Holds concrete HTTP route definition files that the router loads during boot.

### For Humans: What This Means

This is where the actual URL-to-handler mappings live.

## What Belongs Here

Technical: Route PHP files that register endpoints, fallbacks, and simple HTTP responses.

### For Humans: What This Means

Put your route lists here—paths, methods, and handlers.

## What Does NOT Belong Here

Technical: Controllers, middleware implementations, or container wiring.

### For Humans: What This Means

Keep logic elsewhere; these files should only declare routes, not implement business rules.

## How Files Collaborate

Technical: Route files are included by `Http/RouteRegistrar`, which exposes `$router` so registrations land in the
container-managed router.

### For Humans: What This Means

The registrar hands these files the router; they add routes; the app uses them at runtime.
