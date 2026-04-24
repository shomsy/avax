# Router System Overview

## Purpose

This document describes the overall Router system architecture for new team members.

## High-Level Architecture

```

  Application Layer                    
  - Route Registration (DSL)           
  - Route Resolution                   
  - Pipeline Execution                 

                  ↓

  Router Core                          
  - RouterDsl (Registration)           
  - RouterKernel (Execution)           
  - RouterRuntimeInterface             

                  ↓

  Support Layer                        
  - RouteRegistry                      
  - FallbackManager                    
  - RouteConstraintValidator           
  - ErrorResponseFactory               

```

## Ownership Map

| Folder                       | Owner          | Stability |
|------------------------------|----------------|-----------|
| System/Flows/RegisterRoutes  | DSL Team       | Stable    |
| System/Flows/BootstrapRoutes | Bootstrap Team | Stable    |
| System/Flows/ResolveRequest  | Runtime Team   | Stable    |
| System/Flows/RunRoute        | Execution Team | Stable    |
| System/Capabilities          | Core Team      | Stable    |
| System/Configuration         | Config Team    | Stable    |
| System/Foundation/Exceptions | Core Team      | Stable    |

## Key Principles

1. **Flow-First**: Think in flows, not files
2. **BC Guaranteed**: Public API never changes
3. **Documentation = Design**: Every folder has docs
4. **Immutable Routes**: Routes are set after registration
5. **Explicit Errors**: Every failure mode has an exception

## Getting Started

1. Read `docs/Router/System/how-this-works.md`
2. Review flow-specific docs in `docs/Router/System/Flows/`
3. Check `docs/Router/System/Capabilities/` for core concepts
4. See `docs/Router/System/Foundation/Exceptions/` for error handling
