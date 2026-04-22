# Ownership Model

The container now supports authored ownership metadata on every registration.

## Authored Metadata

Each `ServiceRegistration` can carry:

- `unitId`
- `ownerSlice`
- `category`
- `visibility`
- `profiles`
- `flags`
- `tenants`
- `regions`
- `modes`
- `overrideSource`
- `reason`
- `intent`
- `provenance`
- `exported`
- `imports`
- `concept`
- `fallback`

Example:

```php
$container->singleton(PaymentGateway::class, StripeGateway::class)
    ->asCapability('capability.payments')
    ->asShared()
    ->export()
    ->because('Expose one shared payment gateway to importing flows.')
    ->provenance('PaymentsServiceProvider');
```

## Category Vocabulary

Allowed category values:

- `flow`
- `capability`
- `configuration`
- `foundation`

## Visibility Vocabulary

Allowed visibility values:

- `private`
- `shared`
- `public`
- `internal`

Rules:

- `private`: stays inside the owning slice
- `internal`: implementation detail of the owning slice
- `shared`: reusable across slices only when explicitly exported and imported
- `public`: part of the stable top-level surface

Flow owners remain valid top-level entry points even when their internals stay local.

## Exports And Imports

Cross-slice access is explicit:

- provider slice exports a shared unit with `export()`
- consumer slice declares `import('capability.payments')`
- validation and runtime diagnostics explain why access is allowed or blocked

If a shared unit is not exported, or a consumer slice does not import the provider slice, cross-slice access is rejected
by validation and can fail fast at runtime.

## Explainability Surface

Ownership information shows up in:

- `describeService()`
- `debugService()`
- `debugGraph()`
- `validate()`

This means the container can explain:

- who owns a unit
- where it belongs
- whether it is public, shared, private, or internal
- whether current environment, flags, tenant, region, and mode activate or exclude it
- whether the abstract was rebound and what it replaced
- why cross-slice access succeeded or failed
- which units depend on it
- what else is impacted if it changes

## Slice Views

`forSlice()` returns a container facade scoped to one slice:

```php
$billing = $container->forSlice('flow.billing');
$payment = $billing->get(PaymentGateway::class);
```

The slice view uses `SliceContext` to filter resolution and diagnostics to the
services visible from the named slice. Registration writes are not blocked but
inherit the slice context for ownership-aware tracing.

Slice views compose with context views:

```php
$container->forSlice('flow.billing')->forContext(['tenant' => 'acme']);
```

See also: [`slice-view-contracts.md`](./slice-view-contracts.md)

## Compile Boundary

Ownership truth stays authored in `ServiceRegistry`.

The compiled metadata sidecar records derived ownership and slice maps only so that:

- artifact diffs stay honest
- diagnostics stay machine-readable
- validation evidence can be retained

Deleting compiled artifacts never deletes authored ownership truth.
