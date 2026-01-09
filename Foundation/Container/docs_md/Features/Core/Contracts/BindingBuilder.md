# BindingBuilder

## Quick Summary
- Fluent interface for configuring a single binding.
- Lets you set concrete, tags, and constructor arguments.
- Exists to make registration expressive without exposing internal definition objects.

### For Humans: What This Means
It’s the “settings menu” for one service binding.

## Terminology
- **Concrete**: What actually produces the service.
- **Tags**: Labels used to group services.
- **Arguments**: Explicit constructor arguments used during resolution.

### For Humans: What This Means
You use this builder to define “what to build” and “how to label it.”

## Think of It
Like filling out a form when registering a product: model (concrete), categories (tags), custom options (arguments).

### For Humans: What This Means
It’s structured configuration.

## Story Example
You register `MailerInterface` and call `->to(SmtpMailer::class)->tag(['infra'])->withArgument('host', 'smtp.local')`.

### For Humans: What This Means
You configure a binding step-by-step.

## For Dummies
- Call `to()` to set concrete.
- Call `tag()` to add labels.
- Call `withArguments()` or `withArgument()` to set explicit args.

### For Humans: What This Means
It’s a fluent configuration helper.

## How It Works (Technical)
Implementations mutate an underlying definition object and return `$this` for chaining.

### For Humans: What This Means
Each call updates the binding settings.

## Architecture Role
Builder contract used by `RegistryInterface` registration methods.

### For Humans: What This Means
It’s part of the registration experience.

## Methods


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: to(string|callable|null $concrete): self

#### Technical Explanation
Sets the concrete implementation or factory.

##### For Humans: What This Means
Defines what the binding produces.

##### Parameters
- `string|callable|null $concrete`

##### Returns
- `self`

##### Throws
- None.

##### When to Use It
When setting implementation.

##### Common Mistakes
Passing invalid callables.

### Method: tag(string|array $tags): self

#### Technical Explanation
Adds tags.

##### For Humans: What This Means
Label the service.

##### Parameters
- `string|array $tags`

##### Returns
- `self`

##### Throws
- None.

##### When to Use It
Grouping.

##### Common Mistakes
Using too many inconsistent tags.

### Method: withArguments(array $arguments): self

#### Technical Explanation
Sets multiple named args.

##### For Humans: What This Means
Provide constructor overrides.

##### Parameters
- `array $arguments`

##### Returns
- `self`

##### Throws
- None.

##### When to Use It
Binding requires scalar config.

##### Common Mistakes
Using wrong parameter names.

### Method: withArgument(string $name, mixed $value): self

#### Technical Explanation
Sets one argument.

##### For Humans: What This Means
Override one parameter.

##### Parameters
- `string $name`
- `mixed $value`

##### Returns
- `self`

##### Throws
- None.

##### When to Use It
Single override.

##### Common Mistakes
Expecting it to affect property injection.

## Risks, Trade-offs & Recommended Practices
- **Practice: Keep arguments explicit**. Use args for scalar config, not for object graph.

### For Humans: What This Means
Use it for configuration knobs, not for bypassing DI.

## Related Files & Folders
- `docs_md/Features/Core/Contracts/RegistryInterface.md`: Where builders come from.
- `docs_md/Features/Define/Store/ServiceDefinition.md`: Underlying model.

### For Humans: What This Means
Builder methods ultimately configure definitions.
