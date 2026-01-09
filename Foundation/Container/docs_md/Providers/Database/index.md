# Providers/Database

## What This Folder Represents
This folder contains providers that wire database-related services into the container.

Technically, these providers register database clients, connection factories, repositories, and related configuration bindings. They typically choose lifetimes carefully (often singleton for connection factories, scoped for request-bound transactions).

### For Humans: What This Means
It’s the “teach the container how to talk to the database” folder.

## What Belongs Here
- Providers that register database connectivity and supporting services.

### For Humans: What This Means
If it helps your app obtain DB connections/repositories through DI, it belongs here.

## What Does NOT Belong Here
- SQL migrations and schema files.
- Application repositories and domain models (those belong outside the container component).

### For Humans: What This Means
Providers wire infrastructure, they don’t contain your database content.

## How Files Collaborate
Database providers register base DB services. Higher-level application services can then typehint DB interfaces and let the container inject the correct implementation.

### For Humans: What This Means
Once the provider is installed, your services can just ask for “a database thing” and get it.

