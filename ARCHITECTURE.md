# Container Kernel Architecture

## Overview

The Avax Container has been refactored from a monolithic dependency injection system into a modern, modular **Kernel + Pipeline + Facade** architecture. This enterprise-grade design provides clean separation of concerns, enhanced testability, and future extensibility while maintaining full backward compatibility.

## Architecture Components

### 1. Container Facade (`Container.php`)
**Role**: PSR-11 compliant public API
**Responsibilities**:
- Provide ergonomic, fluent API for developers
- Maintain backward compatibility
- Delegate all operations to the Kernel
- Include convenience traits for common operations

**Key Features**:
- Implements PSR-11 `ContainerInterface`
- Uses ergonomic traits (`ExposesDesignFlow`, `ExposesPolicyFlow`, etc.)
- Thin facade layer with no business logic

### 2. Container Kernel (`ContainerKernel.php`)
**Role**: Orchestrator and state manager
**Responsibilities**:
- Manage container lifecycle and state
- Coordinate resolution operations
- Provide dual-mode operation (Legacy/Pipeline)
- Own Flow objects (DesignFlow, PolicyFlow, etc.)

**Key Features**:
- Factory methods for different initialization modes
- Legacy mode for backward compatibility
- Pipeline mode for new architecture
- Unified API regardless of internal implementation

### 3. Resolution Pipeline (`ResolutionPipeline.php`)
**Role**: Orchestrate service resolution steps
**Responsibilities**:
- Execute resolution steps in sequence
- Handle step failures and error propagation
- Provide execution context and metadata
- Enable step customization and reordering

**Key Features**:
- Immutable step sequence
- Comprehensive error handling
- Step timing and diagnostics
- Fluent builder API

## Pipeline Steps

The resolution pipeline consists of 7 modular steps, each with a single responsibility:

### 1. AnalyzePrototypeStep
**Purpose**: Dependency analysis and preparation
- Analyzes service class structure
- Extracts constructor, property, and method dependencies
- Stores analysis results in context metadata
- Enables optimization decisions in later steps

### 2. GuardPolicyStep
**Purpose**: Security and policy enforcement
- Validates service access permissions
- Checks namespace restrictions
- Enforces resolution depth limits
- Provides early failure for unauthorized access

### 3. ResolveInstanceStep
**Purpose**: Core service instantiation
- Creates service instances using ResolutionEngine
- Handles complex dependency resolution
- Applies constructor injection
- Manages singleton/scoped/transient lifecycles

### 4. InjectDependenciesStep
**Purpose**: Property and method injection
- Performs setter injection
- Handles property injection
- Supports interface-based injection
- Manages circular dependency scenarios

### 5. InvokePostConstructStep
**Purpose**: Lifecycle hook execution
- Calls initialization methods
- Supports common patterns (`init`, `setup`, etc.)
- Handles method invocation errors gracefully
- Enables post-construction setup

### 6. StoreLifecycleStep
**Purpose**: Scope and lifecycle management
- Stores instances according to lifecycle policies
- Manages singleton global storage
- Handles scoped instance cleanup
- Supports transient instances

### 7. CollectDiagnosticsStep
**Purpose**: Metrics and telemetry collection
- Records resolution performance metrics
- Tracks step execution times
- Collects dependency complexity data
- Enables monitoring and optimization

## Usage Patterns

### Legacy Mode (Backward Compatible)
```php
// Existing code continues to work unchanged
$container = ContainerBootstrapper::create()->build();
$service = $container->get('MyService');
```

### Pipeline Mode (New Architecture)
```php
// Explicit pipeline configuration
$pipeline = ResolutionPipelineBuilder::create()
    ->withAnalyzer($analyzer)
    ->withGuard($guard)
    ->withEngine($engine)
    ->withInjector($injector)
    ->withInvoker($invoker)
    ->withScopeManager($scopeManager)
    ->withMetrics($metrics)
    ->buildDefault();

$kernel = ContainerKernel::pipeline($pipeline);
$container = new Container($kernel);
```

### Factory Method Usage
```php
// Legacy mode with all existing features
$kernel = ContainerKernel::legacy(
    $definitions, $scopes, $engine, /* ... all deps */
);

// Pipeline mode for new implementations
$kernel = ContainerKernel::pipeline($customPipeline);
```

## Benefits

### 1. **Modularity**
- Each pipeline step is independently testable
- Steps can be reordered or replaced without affecting others
- New resolution strategies can be added easily

### 2. **Testability**
- Individual steps can be unit tested in isolation
- Pipeline can be tested with mock steps
- Context passing enables comprehensive testing

### 3. **Observability**
- Detailed metrics for each resolution step
- Performance timing and bottleneck identification
- Comprehensive diagnostics for debugging

### 4. **Maintainability**
- Single responsibility principle applied throughout
- Clean dependency injection with explicit contracts
- Easy to understand and modify individual components

### 5. **Extensibility**
- New steps can be added to the pipeline
- Custom pipelines for specialized use cases
- Plugin architecture for third-party extensions

### 6. **Performance**
- Optimized resolution flow with early exits
- Cachable analysis results
- Reduced object creation overhead

## Migration Strategy

### Phase 1: Parallel Development ✅
- New architecture developed alongside existing code
- No breaking changes during development
- Comprehensive testing ensures compatibility

### Phase 2: Gradual Migration
- Legacy mode provides backward compatibility
- New features use pipeline mode
- Gradual migration of existing code

### Phase 3: Full Adoption
- Complete migration to pipeline architecture
- Remove legacy code paths
- Optimize for pipeline-only operation

## Quality Assurance

### Testing Strategy
- Unit tests for individual pipeline steps
- Integration tests for complete resolution workflows
- Performance benchmarks comparing modes
- Backward compatibility regression tests

### Code Quality
- PSR-12 compliance maintained
- Comprehensive PHPDoc documentation
- Clean architecture with dependency injection
- Error handling and logging throughout

## Future Enhancements

### Potential Extensions
1. **Conditional Steps** - Steps that execute based on context
2. **Parallel Resolution** - Concurrent dependency resolution
3. **Caching Layers** - Advanced caching strategies
4. **Monitoring Integration** - External monitoring system hooks
5. **Custom Step Libraries** - Reusable step implementations

### Performance Optimizations
1. **JIT Compilation** - Compile pipelines to optimized code
2. **Shared Pipelines** - Reuse pipeline instances across containers
3. **Lazy Analysis** - Defer analysis until first resolution
4. **Result Memoization** - Cache complete resolution results

## Conclusion

The Container Kernel architecture represents a significant advancement in dependency injection system design. By separating orchestration from execution, providing modular pipeline steps, and maintaining backward compatibility, the system achieves enterprise-grade quality while remaining developer-friendly.

The architecture is production-ready, extensively tested, and designed for long-term maintainability and extensibility.