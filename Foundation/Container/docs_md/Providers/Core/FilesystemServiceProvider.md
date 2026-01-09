# FilesystemServiceProvider

## Quick Summary
- This file registers filesystem/storage services into the container.
- It exists so your application can request storage services through DI instead of constructing them manually.
- It removes the complexity of “which storage implementation do we use?” by binding interfaces to implementations centrally.

### For Humans: What This Means
It’s the provider that makes “give me storage” work as a container request.

## Terminology (MANDATORY, EXPANSIVE)
- **Filesystem service**: A service that provides file storage operations.
  - In this file: `Filesystem` is registered as a singleton.
  - Why it matters: many parts of the app want a single consistent filesystem abstraction.
- **Storage interface**: A contract for storing files.
  - In this file: `FileStorageInterface` is bound to `LocalFileStorage`.
  - Why it matters: you can swap local storage for S3, etc., later.
- **Alias**: A string id that resolves to a service.
  - In this file: `'Storage'` resolves to `Filesystem`.
  - Why it matters: convenience and backwards compatibility.

### For Humans: What This Means
This provider gives you a stable way to get file storage, even if you change where files really live later.

## Think of It
Think of it like choosing a delivery service. Your app just says “deliver this package”, and the provider decides whether that’s local pickup or a courier.

### For Humans: What This Means
You can keep your app code the same while changing the underlying storage.

## Story Example
An uploader service needs file storage. It typehints `Filesystem` (or asks for `'Storage'`). This provider ensures the container can build that dependency and that it uses a local storage implementation by default.

### For Humans: What This Means
Your app doesn’t need to know the plumbing of storage—just how to use it.

## For Dummies

This section gives you a slow, step-by-step mental model and a beginner-safe walkthrough of what the file does.

### For Humans: What This Means
If you’re new to this area, read this first. It helps you avoid getting lost in terminology and lets you use the code with confidence.

1. Provider runs during boot.
2. It binds `FileStorageInterface` to `LocalFileStorage`.
3. It registers `Filesystem`.
4. It adds a `'Storage'` alias for convenience.

## How It Works (Technical)
`register()` uses container singleton bindings to wire storage interface → local implementation and to register the `Filesystem` service. The alias closure resolves the `Filesystem` from the container.

### For Humans: What This Means
It sets up “one filesystem service” and gives it a nickname.

## Architecture Role
- Why this file lives in `Providers/Core`: storage is infrastructure.
- What depends on it: uploaders, asset managers, caches, and other IO-heavy services.
- What it depends on: filesystem library classes and the container registration API.
- System-level reasoning: central wiring enables easy backend replacement.

### For Humans: What This Means
If you ever switch storage backends, you want to change one provider, not your whole app.

## Methods (MANDATORY)


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: register(…)

#### Technical Explanation
Registers storage bindings and a `'Storage'` alias.

##### For Humans: What This Means
It installs filesystem services into the container.

##### Parameters
- None.

##### Returns
- Returns nothing.

##### Throws
- No explicit exceptions.

##### When to Use It
- Called by your bootstrap sequence.

##### Common Mistakes
- Assuming `'Storage'` is a global; it’s only available if this provider ran.

## Risks, Trade-offs & Recommended Practices
- Risk: Alias casing (`'Storage'`) can surprise consumers.
  - Why it matters: string ids are easy to mistype.
  - Design stance: prefer class/interface ids when possible.
  - Recommended practice: use `Filesystem::class` as the primary dependency; use alias only when necessary.

### For Humans: What This Means
Aliases are convenient, but types are safer.

## Related Files & Folders
- `docs_md/Providers/Core/index.md`: Core providers chapter.

### For Humans: What This Means
This provider is part of the “core infrastructure” layer you usually bootstrap early.

