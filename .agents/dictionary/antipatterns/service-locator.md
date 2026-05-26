# AntiPattern: Service Locator

## What It Is

Runtime code pulls dependencies from a container or global registry instead of receiving explicit dependencies.

## Symptoms

- `->get(SomeClass::class)`
- `->make(SomeClass::class)`
- `ContainerInterface` inside business/runtime code
- `ServiceLocator`
- `self::resolve`
- `static::resolve`

## Why It Is Dangerous

It hides required dependencies, delays failure, and breaks worker/runtime safety.

## Common AI Failure Mode

An agent uses the container as a shortcut to avoid constructor design.

## How to Fix

Move construction to Configuration, Assembly, Provider, Factory, or Builder boundaries.

## Allowed Exceptions

Composition root and service providers with explicit ownership.

## Severity

BLOCKER in production behavior. MEDIUM in tooling when justified.
