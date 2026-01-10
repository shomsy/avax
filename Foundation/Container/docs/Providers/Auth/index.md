# Providers/Auth

## What This Folder Represents
This folder contains service providers for authentication and security-related container bindings.

Technically, these providers register bindings for authentication helpers, security policies, and any per-request/scoped auth state required by your application infrastructure. They keep auth wiring isolated so you can enable/disable or swap implementations cleanly.

### For Humans: What This Means (Represent)
It’s the “teach the container about auth” folder.

## What Belongs Here
- Providers that register authentication-related services.
- Providers that register security helpers/policies related to resolution.

### For Humans: What This Means (Belongs)
If it helps your app build and use authentication services, it belongs here.

## What Does NOT Belong Here
- Actual application user/business logic.
- Core container kernel and resolution steps.

### For Humans: What This Means (Not Belongs)
Providers wire; they don’t implement your business rules.

## How Files Collaborate
Auth providers register their bindings into the container during boot. Other providers and runtime components then rely on those bindings for request handling and security enforcement.

### For Humans: What This Means (Collaboration)
Providers are “setup chapters” that other parts of the system can rely on.

