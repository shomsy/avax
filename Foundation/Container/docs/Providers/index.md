# Providers

## What This Folder Represents
This folder contains service providers that register common integrations into the container.

Technically, providers are boot-time registration modules: each provider is responsible for adding a set of bindings/definitions into the container for a particular subsystem (HTTP, auth, database, filesystem, logging, etc.). They keep integration wiring isolated and composable.

### For Humans: What This Means (Represent)
Providers are like plug-in installers. You enable one, and the container learns how to build and wire a whole feature area.

## What Belongs Here
- Service provider classes that register bindings and configuration.
- Provider subfolders grouped by domain (HTTP, Auth, Database, Core).

### For Humans: What This Means (Belongs)
If it’s “how do we register this subsystem into the container?”, it’s a provider.

## What Does NOT Belong Here
- The container’s core resolution engine.
- Application-specific business code.

### For Humans: What This Means (Not Belongs)
Providers wire things up; they don’t *own* the business logic.

## How Files Collaborate
Bootstrapping code selects and runs providers. Providers call registration APIs (bind/singleton/scoped, contextual bindings, extenders) to populate `DefinitionStore`. The runtime engine then resolves services using those definitions.

### For Humans: What This Means (Collaboration)
Providers “teach” the container about your app’s subsystems. The runtime then uses those lessons.

