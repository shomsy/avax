# AvaX Extension and Plugin Architecture

**Date:** 2026-05-07
**Stage:** Stage 13 — Extension and Plugin Architecture
**Status:** ACTIVE

---

## 0. Purpose

This document defines the rules for creating, discovering, loading, and isolating extensions (plugins, modules, external
packages) within the AvaX framework. AvaX favors explicit architectural integration over magical hooks or hidden event
listeners.

---

## 1. Extension Philosophy

AvaX does not support "Drop-In Magic Plugins".
All extensions must be explicitly assembled via the `ApplicationBuilder` during kernel boot.

### Why No Magic?

- It hides the flow of dependencies.
- It breaks the `Screaming Architecture` by introducing unexpected behaviors at runtime.
- It makes debugging state mutations and capability resolution significantly harder.

---

## 2. The `ComponentProviderInterface`

The primary mechanism for any extension to integrate with AvaX is by implementing
`Avax\Framework\System\Capabilities\ComponentRegistry\ComponentProviderInterface`.

Extensions must provide a concrete implementation of this interface, which exposes:

1. `name()` — The canonical name of the extension suite.
2. `boot(RuntimeInterface $runtime)` — The lifecycle hook where the extension registers its capabilities, configuration
   schemas, and flows.

### Example Extension Provider

```php
namespace Vendor\Plugin\System\Configuration;

use Avax\Framework\System\Capabilities\ComponentRegistry\ComponentProviderInterface;
use Avax\Framework\System\Capabilities\Runtime\RuntimeInterface;

final class PaymentExtensionProvider implements ComponentProviderInterface
{
    public static function name(): string
    {
        return 'Vendor/PaymentExtension';
    }

    public function boot(RuntimeInterface $runtime): void
    {
        // 1. Register specific flows or capabilities to the runtime
        // 2. Bind specific HTTP routes or console commands
        // 3. Register implementations into the DI container
    }
}
```

---

## 3. Registering Extensions

Extensions are explicitly registered in the application's boot sequence (`index.php` or `console`):

```php
$builder = new ApplicationBuilder(
    projectPath: new ProjectPath(__DIR__),
    environmentName: new EnvironmentName('production'),
);

// Explicit extension registration
$builder->registerComponentProvider(new PaymentExtensionProvider());

$avax = Avax::boot($builder);
```

---

## 4. Extension Internal Architecture

Extensions intended for AvaX must follow the exact same architectural rules as core AvaX components.

The extension must have a canonical shape:

```text
vendor/plugin/src/System/
  PublicSurface/    -> Publicly accessible classes / contracts of the plugin.
  Flows/            -> End-to-end use cases provided by the plugin.
  Capabilities/     -> Individual, reusable business rules and adapters.
  Configuration/    -> Plugin configuration definitions and the ComponentProviderInterface.
  Foundation/       -> (Optional) Plugin-specific domain values, errors.
```

If an extension does not follow this structure, it is considered a generic PHP library, not an AvaX-native Component
Suite. AvaX does not forbid standard PHP libraries; they can be pulled via Composer and manually bound inside the
application's configuration. However, to be treated as a first-class AvaX Extension, the architectural rules apply.

---

## 5. Overriding Core Behavior

Extensions **may not** replace core framework implementations via magical DI reflection.
If an extension needs to provide a custom implementation of an `Interface` (e.g., custom Caching or Logging driver), it
must do so by providing the driver to the respective configuration system, NOT by trying to hack the `ComponentRegistry`
directly.

---

## 6. Security and Isolation

- Extensions do not have access to the internal `state()` mutations unless explicitly exposed by the `RuntimeKernel`.
- Extensions must register their own `StateReset` rules if they manage long-lived state in worker models.
- Any HTTP routes provided by extensions must be explicitly mounted by the consumer. They are not automatically mapped.

---

## 7. Versioning and Compatibility

AvaX extensions must declare compatibility with AvaX Framework semantic version targets in their `composer.json`.
An extension using deprecated APIs (`@deprecated`) must migrate to the new `PublicSurface` APIs before the next major
version of AvaX is released.
