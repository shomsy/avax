# BindingBuilder

## Quick Summary

- Fluent interface for configuring a single binding.
- Lets you set concrete, tags, and constructor arguments.
- Exists to make registration expressive without exposing internal definition objects.

### For Humans: What This Means (Summary)

It’s the “settings menu” for one service binding.

## Terminology (MANDATORY, EXPANSIVE)

- **Concrete**: What actually produces the service.
- **Tags**: Labels used to group services.
- **Arguments**: Explicit constructor arguments used during resolution.

### For Humans: What This Means (Terminology)

You use this builder to define “what to build” and “how to label it.”

## Think of It

Like filling out a form when registering a product: model (concrete), categories (tags), custom options (arguments).

### For Humans: What This Means (Analogy)

It’s structured configuration.

## Story Example

You register `MailerInterface` and call `->to(SmtpMailer::class)->tag(['infra'])->withArgument('host', 'smtp.local')`.

### For Humans: What This Means (Story)

You configure a binding step-by-step.

## For Dummies

- Call `to()` to set concrete.
- Call `tag()` to add labels.
- Call `withArguments()` or `withArgument()` to set explicit args.

### For Humans: What This Means (Walkthrough)

It’s a fluent configuration helper.

## How It Works (Technical)

Implementations mutate an underlying definition object and return `$this` for chaining.

### For Humans: What This Means (Technical)

Each call updates the binding settings.

## Architecture Role

Builder contract used by `RegistryInterface` registration methods.

### For Humans: What This Means (Architecture)

It’s part of the registration experience.

## Methods

This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means (Methods)

When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: to(string|callable|null $concrete): self

#### Technical Explanation (to)

Sets the concrete implementation or factory.

##### For Humans: What This Means (to)

Defines what the binding produces.

##### Parameters (to)

- `string|callable|null $concrete`

##### Returns (to)

- `self`

##### Throws (to)

- None.

##### When to Use It (to)

When setting implementation.

##### Common Mistakes (to)

Passing invalid callables.

### Method: tag(string|array $tags): self

#### Technical Explanation (tag)

Adds tags.

##### For Humans: What This Means (tag)

Label the service.

##### Parameters (tag)

- `string|array $tags`

##### Returns (tag)

- `self`

##### Throws (tag)

- None.

##### When to Use It (tag)

Grouping.

##### Common Mistakes (tag)

Using too many inconsistent tags.

### Method: withArguments(array $arguments): self

#### Technical Explanation (withArguments)

Sets multiple named args.

##### For Humans: What This Means (withArguments)

Provide constructor overrides.

##### Parameters (withArguments)

- `array $arguments`

##### Returns (withArguments)

- `self`

##### Throws (withArguments)

- None.

##### When to Use It (withArguments)

Binding requires scalar config.

##### Common Mistakes (withArguments)

Using wrong parameter names.

### Method: withArgument(string $name, mixed $value): self

#### Technical Explanation (withArgument)

Sets one argument.

##### For Humans: What This Means (withArgument)

Override one parameter.

##### Parameters (withArgument)

- `string $name`
- `mixed $value`

##### Returns (withArgument)

- `self`

##### Throws (withArgument)

- None.

##### When to Use It (withArgument)

Single override.

##### Common Mistakes (withArgument)

Expecting it to affect property injection.

## Risks, Trade-offs & Recommended Practices

- **Practice: Keep arguments explicit**. Use args for scalar config, not for object graph.

### For Humans: What This Means (Risks)

Use it for configuration knobs, not for bypassing DI.

## Related Files & Folders

- `docs_md/Features/Core/Contracts/RegistryInterface.md`: Where builders come from.
- `docs_md/Features/Define/Store/ServiceDefinition.md`: Underlying model.

### For Humans: What This Means (Relationships)

Builder methods ultimately configure definitions.
