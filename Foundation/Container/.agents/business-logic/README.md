# Business Logic — Foundation/Container

This folder captures the meaning of this component in plain language.

## What This Component Does

`Foundation/Container` is a dependency injection container for the Avax
component stack. It resolves services, applies guards, injects dependencies,
and coordinates lifecycle and diagnostics through the kernel/pipeline model.

## What Belongs Here

- the user and operator outcome the container exists to support
- the boundaries between facade, kernel, pipeline, and support services
- the rules that define safe resolution and injection behavior
- terms that matter to people reading the component without opening code

## Required Questions

Every business note or domain rule should answer:

1. What outcome should the user or operator achieve?
2. What should the software do to make that outcome reliable?
3. What should be visible or materially different at the boundary?
4. What should the user or operator understand, trust, or be able to do afterwards?

## Rule

- Write user-facing meaning before implementation detail.
- Do not reuse `business-logic/` between projects; it is always local.
