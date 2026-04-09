# Testing Guidance for the New Wave

This document provides testing guidance for slice views, pooled lifetime, and new diagnostics features.

## Testing Slice Visibility

### How to Test Slice Filtering

```php
// Test that debugGraph filters to slice
$container->singleton(PaymentGateway::class, StripeGateway::class)
    ->asCapability('capability.payments')
    ->asShared()
    ->export();

$container->bind(LoginFlow::class, LoginFlow::class)
    ->asFlow('flow.login')
    ->asPrivate()
    ->import('capability.payments');

$graph = $container->debugGraph('flow.login');

// Assert flow sees its own services
assert(in_array('LoginFlow', $graph['manifest']['services']));

// Assert flow sees imported capability
assert(in_array('capability.payments', $graph['manifest']['imports']));

// Assert flow does NOT see other flows
assert(!isset($graph['manifest']['services']['CheckoutFlow']));
```

### How to Test Cross-Slice Access Violations

```php
// Attempt cross-slice access without proper import
$container->bind(LoginFlow::class, LoginFlow::class)
    ->asFlow('flow.login')
    ->asPrivate(); // No import declared

$issues = $container->validate(['flow.login']);

// Should report missing import
assert(count($issues['ownership']) > 0);
assert(str_contains($issues['ownership'][0]['message'], 'capability.payments'));
```

### How to Test Export/Import Contracts

```php
// Test that export makes service accessible
$container->singleton(PaymentGateway::class, StripeGateway::class)
    ->asCapability('capability.payments')
    ->asShared()
    ->export();

$container->bind(LoginFlow::class, LoginFlow::class)
    ->asFlow('flow.login')
    ->import('capability.payments');

// This should work - capability is imported
$gateway = $container->get(PaymentGateway::class);
assert($gateway instanceof StripeGateway);

// Test that unexported service is inaccessible
$container->singleton(SecretService::class, InternalSecret::class)
    ->asCapability('capability.secrets')
    ->asShared(); // NOT exported

$container->bind(LoginFlow::class, LoginFlow::class)
    ->asFlow('flow.login');

$issues = $container->validate(['flow.login']);
// Should warn about unexported capability access
```

## Testing Pooled Lifetime Safely

### How to Test Pool Reset Behavior

```php
// Test that pooled services reset before reuse
class MockParser implements ResettableInterface
{
    public int $resetCount = 0;
    
    public function reset(): void
    {
        $this->resetCount++;
    }
}

$container->pooled(MockParser::class)->resettable();

$parser1 = $container->get(MockParser::class);
$container->reset(); // Reset the pool
$parser2 = $container->get(MockParser::class);

// Parser was reset between uses
assert($parser1 === $parser2); // Same instance, but reset
assert($parser1->resetCount === 1);
```

### How to Test Pool Overflow

```php
// Test max pool size enforcement
$container->pooled(HttpClient::class)
    ->maxPoolSize(2)
    ->onOverflow(OverflowStrategy::FAIL);

$client1 = $container->get(HttpClient::class);
$client2 = $container->get(HttpClient::class);

// Third request should fail
try {
    $client3 = $container->get(HttpClient::class);
    fail('Should have thrown');
} catch (ContainerException $e) {
    assert(str_contains($e->getMessage(), 'pool'));
}
```

### How to Test Pool Diagnostics

```php
// Test pool statistics are reported
$container->pooled(Parser::class)->resettable();

// Use the service multiple times
for ($i = 0; $i < 5; $i++) {
    $container->get(Parser::class);
}

$report = $container->runtimeReport();
$stats = $report['poolStats']['parser'] ?? null;

assert($stats !== null);
assert($stats['hits'] >= 4); // Reused from pool
assert($stats['resets'] >= 4); // Reset each time
```

### How to Test Unsafe Pool Detection

```php
// Test that non-resettable services warn
class NonResettableService 
{
    private array $data = [];
    public function setData(array $data) { $this->data = $data; }
}

$container->pooled(NonResettableService::class);

$issues = $container->validate();
assert(count($issues['lifetime']) > 0);
assert(str_contains($issues['lifetime'][0]['message'], 'resettable'));
```

## Testing Anti-Pattern Enforcement

### How to Test Shared Captures Scoped

```php
$container->scoped(ScopedService::class);
$container->singleton(SharedService::class)->dependsOn(ScopedService::class);

$issues = $container->validate();
assert(count($issues['lifetime']) > 0);
assert(str_contains($issues['lifetime'][0]['message'], 'shared'));
```

### How to Test Disposable Transient

```php
class DisposableTransient implements DisposableInterface
{
    public function dispose(): void {}
}

$container->transient(DisposableTransient::class);

$issues = $container->validate();
assert(count($issues['lifetime']) > 0);
assert(str_contains($issues['lifetime'][0]['message'], 'transient'));
```

### How to Test Duplicate Concepts

```php
$container->singleton(PaymentGateway::class, StripeGateway::class)
    ->concept('payment-provider');

$container->singleton(AlternativePayment::class, PayPalGateway::class)
    ->concept('payment-provider'); // Same concept!

$issues = $container->validate();
assert(count($issues['ownership']) > 0);
assert(str_contains($issues['ownership'][0]['message'], 'duplicate concept'));
```

### How to Test Cyclic Dependencies

```php
$container->singleton(ServiceA::class);
$container->singleton(ServiceB::class);

// Create cycle
$container->singleton(ServiceA::class)->dependsOn(ServiceB::class);
$container->singleton(ServiceB::class)->dependsOn(ServiceA::class);

$issues = $container->validate();
assert(count($issues['lifetime']) > 0);
assert(str_contains($issues['lifetime'][0]['message'], 'cycle'));
```

## Testing Compiled/Runtime Parity

### How to Test Compile Mode Coverage

```php
// Test that compiled path produces same results as runtime
$container->compileContainer();

$runtimeResult = $container->get(Service::class);
$compiledResult = $container->get(Service::class);

assert($runtimeResult instanceof $compiledResult);
assert(spl_object_id($runtimeResult) === spl_object_id($compiledResult));
```

### How to Test Slice Metadata Parity

```php
$container->singleton(PaymentGateway::class, StripeGateway::class)
    ->asCapability('capability.payments')
    ->asShared()
    ->export();

$container->compileContainer();

// Verify slice manifest in compiled artifacts
$report = $container->compileReport();
$derivedSlices = $report['derivedSlices'] ?? [];

assert(isset($derivedSlices['capability.payments']));
assert($derivedSlices['capability.payments']['visibility'] === 'shared');
```

### How to Test Ownership Metadata Parity

```php
$container->singleton(Service::class)
    ->asCapability('capability.test')
    ->export()
    ->reason('Test capability')
    ->provenance('TestProvider');

// Compile
$container->compileContainer();

// Verify ownership preserved
$describe = $container->describeService(Service::class);
assert($describe['reason'] === 'Test capability');
assert($describe['provenance'] === 'TestProvider');
```

## Test Organization

### Smoke Tests Location

```
tests/
├── Flows/
│   ├── ResolveService/
│   │   ├── SliceViewSmokeTest.php
│   │   ├── OwnershipCompositionSmokeTest.php
│   │   └── PooledLifetimeSmokeTest.php
│   └── RegisterServices/
│       └── RegisterServicesSmokeTest.php
├── Capabilities/
│   ├── Declaration/
│   ├── Execution/
│   ├── Runtime/
│   └── Diagnostics/
```

### Integration Tests

```
tests/Integration/
├── SliceCompositionTest.php       # Cross-slice composition
├── PooledLifetimeTest.php         # Pool reset behavior
├── CompiledSliceMetadataTest.php  # Compile/runtime parity
└── PolicyEnforcementTest.php      # Anti-pattern detection
```

## Test Helpers

### TestComposition Extensions

The `TestComposition` helper supports slice-aware testing:

```php
$composition = TestComposition::create();

$composition->singletonCapability(
    'capability.payments',
    PaymentGateway::class,
    StripeGateway::class,
    exported: true,
    imports: []
);

$composition->bindFlow(
    'flow.checkout',
    CheckoutFlow::class,
    CheckoutFlow::class,
    entry: true,
    imports: ['capability.payments']
);

// Verify slice view
$graph = $composition->debugGraph('flow.checkout');
assert(count($graph['manifest']['services']) > 0);
```

### Diagnostic Test Helpers

```php
// Helper for asserting validation issues
function assertValidationIssue(array $issues, string $type, string $messageContains): void
{
    $found = false;
    foreach ($issues as $issue) {
        if ($issue['type'] === $type && str_contains($issue['message'], $messageContains)) {
            $found = true;
            break;
        }
    }
    assert($found, "Expected issue type '$type' with message containing '$messageContains'");
}
```

## Benchmark Integration

### Slice View Benchmarks

```
bench_slice_view_flow:        debugGraph for single flow
bench_slice_view_capability:  debugGraph for single capability
bench_slice_view_root:         debugGraph for full graph
bench_validate_slice:         validate() for one slice
bench_validate_full:          validate() for all slices
```

### Pooled Lifetime Benchmarks

```
bench_pooled_reuse:            reuse pooled instance
bench_pooled_reset:            reset and reuse
bench_pooled_overflow_evict:   pool eviction under load
bench_pooled_overflow_fail:    pool overflow failure
bench_pooled_max_size:         pool size limit enforcement
```

### Expected Benchmarks

- Slice view operations: < 5ms for typical graphs
- Validate slice: < 10ms for typical slice
- Pool reuse: < 0.1ms (should be faster than transient create)
- Pool reset: < 0.2ms (should be faster than new instance)

---

*This guidance is owned by the architecture-contract agent. Codex implements test surfaces.*
