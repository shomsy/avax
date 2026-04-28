# ADR 0001: Avax Is a Runtime-Agnostic Framework

## Status

Accepted

## Context

The repository contains reusable components and several bootstrap ideas, but no stable framework-level lifecycle owner.

## Decision

`framework/System` becomes the framework runtime owner. Components remain reusable capabilities that do not assume
PHP-FPM, RoadRunner, FrankenPHP, Swoole, or Workerman.

## Consequences

- Runtime concerns move into `framework/System`.
- Component migration can proceed incrementally.
- Long-lived runtime safety becomes a first-class acceptance criterion.
