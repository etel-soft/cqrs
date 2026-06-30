[![CI Status](https://github.com/etel-soft/cqrs/workflows/CI/badge.svg)](https://github.com/etel-soft/cqrs/actions)
[![codecov](https://codecov.io/gh/etel-soft/cqrs/graph/badge.svg?token=8GD5SQLXAR)](https://codecov.io/gh/etel-soft/cqrs)
[![Latest Stable Version](http://poser.pugx.org/etel/cqrs/v)](https://packagist.org/packages/etel/cqrs)
[![License](http://poser.pugx.org/etel/cqrs/license)](https://packagist.org/packages/etel/cqrs)
[![PHP Version Require](http://poser.pugx.org/etel/cqrs/require/php)](https://packagist.org/packages/etel/cqrs)

# Etel CQRS Component

Contracts and a [Symfony Messenger](https://symfony.com/doc/current/messenger.html)-based implementation of the
CQRS pattern for PHP 8.4+.

The library ships **three buses** — Command, Query and Event — each following the same layering, and lets a handler's
result type be declared **once, on the message**, so that:

- the bus enforces it at runtime (throws on a mismatch);
- static analysis (PHPStan) infers the concrete, optionally-nullable return type of the dispatch call.

## Table of contents

- [Concepts](#concepts)
- [Requirements](#requirements)
- [Installation](#installation)
- [Wiring with Symfony Messenger](#wiring-with-symfony-messenger)
- [Commands](#commands)
- [Queries](#queries)
- [Events](#events)
- [Declaring & checking handler results](#declaring--checking-handler-results)
- [Inputs: raw data before validation](#inputs-raw-data-before-validation)
- [Per-bus options](#per-bus-options)
- [Exceptions](#exceptions)
- [Static analysis (PHPStan)](#static-analysis-phpstan)
- [Development](#development)

## Concepts

The package is split into two layers:

| Layer              | Namespace                                         | Dependencies        |
|--------------------|---------------------------------------------------|---------------------|
| **Contracts**      | `Etel\CQRS\{Command,Query,Event}\`                | None — pure PHP     |
| **Implementation** | `Etel\CQRS\{Command,Query,Event}\Implementation\` | `symfony/messenger` |

The contracts layer has **no external dependencies**; `symfony/messenger` is a `suggest` only. Depend on the contracts
from your domain and only pull the implementation in at the composition root. This keeps your code testable against the
interfaces and lets you swap the transport without touching handlers.

The three buses differ by intent, following Command/Query Separation:

| Bus          | Method      | Result                                | Handlers     |
|--------------|-------------|---------------------------------------|--------------|
| `CommandBus` | `command()` | Optional (fire-and-forget by default) | Exactly one  |
| `QueryBus`   | `query()`   | Always returned (synchronous)         | Exactly one  |
| `EventBus`   | `publish()` | None (`void`)                         | Zero or more |

## Requirements

- PHP **8.4+**
- `symfony/messenger` **^7.0 \|\| ^8.0** (only for the `*\Implementation\` classes)

## Installation

```bash
composer require etel/cqrs
```

To use the Messenger-based implementation, also require Messenger:

```bash
composer require symfony/messenger
```

## Wiring with Symfony Messenger

Each bus is a thin decorator over a Messenger `MessageBusInterface`. Configure one Messenger bus per CQRS bus and
register the decorators.

```yaml
# config/packages/messenger.yaml
framework:
    messenger:
        default_bus: command.bus
        buses:
            command.bus:
                middleware:
                    # 1. translate CommandBusOptions (delay, validation groups) into native stamps
                    - Etel\CQRS\Command\Implementation\Middleware\CommandBusOptionsMiddleware
                    # 2. validate the raw message (Symfony Validator)
                    - validation
                    # 3. transform a validated CommandInput into the final command — AFTER validation
                    - Etel\CQRS\Command\Implementation\Middleware\CommandInputTransformerMiddleware
            query.bus:
                middleware:
                    - Etel\CQRS\Query\Implementation\Middleware\QueryBusOptionsMiddleware
                    - validation
                    - Etel\CQRS\Query\Implementation\Middleware\QueryInputTransformerMiddleware
            event.bus:
                middleware:
                    - Etel\CQRS\Event\Implementation\Middleware\EventBusOptionsMiddleware
```

```yaml
# config/services.yaml
services:
    _defaults:
        autowire: true
        autoconfigure: true

    # Bind each contract to its Messenger-backed implementation.
    Etel\CQRS\Command\CommandBus:
        class: Etel\CQRS\Command\Implementation\MessengerCommandBus
        arguments: ['@command.bus']

    Etel\CQRS\Query\QueryBus:
        class: Etel\CQRS\Query\Implementation\MessengerQueryBus
        arguments: ['@query.bus']

    Etel\CQRS\Event\EventBus:
        class: Etel\CQRS\Event\Implementation\MessengerEventBus
        arguments: ['@event.bus']

    # Middleware are stateless and autowirable.
    Etel\CQRS\Command\Implementation\Middleware\:
        resource: '../vendor/etel/cqrs/src/Command/Implementation/Middleware/'
    Etel\CQRS\Query\Implementation\Middleware\:
        resource: '../vendor/etel/cqrs/src/Query/Implementation/Middleware/'
    Etel\CQRS\Event\Implementation\Middleware\:
        resource: '../vendor/etel/cqrs/src/Event/Implementation/Middleware/'
```

> **Middleware order matters.** The options middleware runs **first** (it emits the `ValidationStamp`/`DelayStamp`
> that later steps rely on); the input transformer runs **after** `validation`, so raw input is validated before it
> becomes the final message.

Mark handlers so the container can autoconfigure them (the library markers are optional but convenient):

```php
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Etel\CQRS\Command\AsCommandHandler;

#[AsCommandHandler] // library marker — tag handlers for your DI compiler pass
#[AsMessageHandler(bus: 'command.bus')] // Symfony Messenger routing
final readonly class CreateUserHandler { /* ... */ }
```

`AsCommandHandler`, `AsQueryHandler` and `AsEventHandler` are plain class markers; wire them to a compiler pass or use
them alongside Messenger's own `#[AsMessageHandler]`.

## Commands

A command expresses intent to change state. By default it is **fire-and-forget**.

```php
use Etel\CQRS\Command\CommandBus;

final readonly class CreateUser
{
    public function __construct(
        public string $email,
        public string $name
    ) {}
}

final readonly class CreateUserHandler
{
    public function __invoke(CreateUser $command): void
    {
        // ... persist ...
    }
}

// Dispatch — returns null, does not wait for a result.
$commandBus->command(command: new CreateUser(email: 'jane@example.com', name: 'Jane'));
```

When you need the handler's result synchronously (e.g. a freshly created id), declare it with `#[CommandResult]` —
see [Declaring & checking handler results](#declaring--checking-handler-results).

```php
use Etel\CQRS\Command\CommandResult;

#[CommandResult(CreatedUser::class)]
final readonly class CreateUser { /* ... */ }

$user = $commandBus->command(command: new CreateUser(/* ... */)); // $user is CreatedUser (statically + at runtime)
```

## Queries

A query reads state and **always** returns a result synchronously (a query that only enqueues work is a command).

```php
use Etel\CQRS\Query\QueryBus;
use Etel\CQRS\Query\QueryResult;

#[QueryResult(UserView::class)]
final readonly class GetUserById
{
    public function __construct(public int $id) {}
}

final readonly class GetUserByIdHandler
{
    public function __invoke(GetUserById $query): UserView
    {
        // ... load and return a UserView ...
    }
}

$user = $queryBus->query(query: new GetUserById(id: 42)); // $user is UserView
```

A nullable result (e.g. "not found") is declared with `nullable: true`:

```php
#[QueryResult(UserView::class, nullable: true)]
final readonly class FindUserByEmail
{
    public function __construct(public string $email) {}
}

$user = $queryBus->query(query: new FindUserByEmail(email: 'jane@example.com')); // UserView|null
```

## Events

An event is a domain fact that already happened. It may have **zero or more** handlers and never returns a result.
Events carry no validation layer by design — their data is guaranteed valid at the point of emission.

```php
use Etel\CQRS\Event\EventBus;

final readonly class UserRegistered
{
    public function __construct(public int $userId) {}
}

$eventBus->publish(event: new UserRegistered(userId: $user->id));
```

## Declaring & checking handler results

The `#[QueryResult]` / `#[CommandResult]` attributes declare a message's result **once, on the message class**. The
bus reads them to verify the handler's return value at runtime, and a bundled PHPStan extension uses them to infer the
dispatch call's return type — so you never repeat the FQCN at the call site.

### Queries

A query always returns; the attribute pins the type:

```php
#[QueryResult(UserView::class)]                 // returns UserView
#[QueryResult(UserView::class, nullable: true)] // returns UserView|null
#[QueryResult]                                  // any result (scalar/array/union) — returns mixed
```

| Attribute                                    | `query()` return type | Runtime check                            |
|----------------------------------------------|-----------------------|------------------------------------------|
| `#[QueryResult(Foo::class)]`                 | `Foo`                 | Result must be a `Foo`, otherwise throws |
| `#[QueryResult(Foo::class, nullable: true)]` | `Foo\|null`           | `Foo` or `null`                          |
| `#[QueryResult]`                             | `mixed`               | none                                     |
| *no attribute*                               | `mixed`               | none                                     |

### Commands

A command is fire-and-forget unless it opts in. **Omitting the attribute means "returns nothing"** (void / async /
always-void handler) — that is the canonical way to say a command has no result:

| Attribute                      | Semantics                                          | `command()` return type                      |
|--------------------------------|----------------------------------------------------|----------------------------------------------|
| *no attribute*                 | Void / fire-and-forget                             | `null`                                       |
| `#[CommandResult(Foo::class)]` | Synchronous result of type `Foo`                   | `Foo` (or `Foo\|null` with `nullable: true`) |
| `#[CommandResult]`             | A result is expected, but not a class (e.g. an id) | `mixed`                                      |

```php
#[CommandResult(CreatedUser::class)]
final readonly class CreateUser { /* ... */ }

$created = $commandBus->command(command: new CreateUser(/* ... */)); // CreatedUser
```

### Runtime guarantees

When a result type is declared, the bus throws if the handler violates it:

- the handler returns the wrong type → `UnexpectedQueryResult` / `UnexpectedCommandResult`;
- the handler returns `null` but the attribute is not `nullable` → same exception;
- the message reaches no handler (e.g. it was routed async) while a result is expected →
  `InvalidQueryReturnConfiguration` / `InvalidCommandReturnConfiguration`.

### `$expectResult`: the per-call escape hatch

Both `command()` and `query()` still accept an explicit `$expectResult` argument. The attribute is the recommended,
declarative source of truth; `$expectResult` exists for cases the attribute cannot cover:

- **third-party messages** you cannot annotate;
- a one-off **override** at a single call site;
- typing that works **without the PHPStan extension** installed.

```php
// Force a typed result for a query you don't own:
$result = $queryBus->query(query: $thirdPartyQuery, expectResult: SomeResult::class); // SomeResult

// Commands accept true to mean "expect any result", false (default) for fire-and-forget:
$any = $commandBus->command(command: $command, expectResult: true);
```

An explicit class-string in `$expectResult` takes precedence over the attribute.

## Inputs: raw data before validation

When a message must contain **valid, strongly-typed** data (non-nullable properties, enums/VOs instead of raw
strings/ints), use an intermediate input DTO. `CommandInput` / `QueryInput` hold raw data and expose
`toCommand()` / `toQuery()`, which either return the final typed message or throw if the data is invalid. The
transformer middleware performs this swap after validation, preserving all stamps.

```php
use Etel\CQRS\Command\CommandInput;

/** @implements CommandInput<CreateUser> */
final readonly class CreateUserInput implements CommandInput
{
    public function __construct(
        public ?string $email = null,
        public ?string $name = null,
    ) {}

    public function toCommand(): CreateUser
    {
        if ($this->email === null || $this->name === null) {
            throw new InvalidCreateUserData(); // implements Etel\CQRS\Command\Exception\InvalidCommandData
        }

        return new CreateUser(email: $this->email, name: $this->name);
    }
}

// Dispatch the input; the middleware validates and transforms it into CreateUser.
$commandBus->command(command: new CreateUserInput(email: $request->email, name: $request->name));
```

Events have no input equivalent: event data is always valid at emission.

## Per-bus options

Each bus takes its own typed options value object instead of raw Messenger stamps. Buses know nothing about each
other's options.

```php
use Etel\CQRS\Command\CommandBusOptions;
use Etel\CQRS\Query\QueryBusOptions;
use Etel\CQRS\Event\EventBusOptions;

// Command: delay (ms) and validation groups.
$commandBus->command(command: $command, options: new CommandBusOptions(
    delayMs: 5000,
    validationGroups: ['Default', 'strict']
));

// Query: validation groups only.
$user = $queryBus->query(query: $query, options: new QueryBusOptions(validationGroups: ['Default']));

// Event: delay and dispatch-after-current-bus.
$eventBus->publish(event: $event, options: new EventBusOptions(
    delayMs: 1000,
    dispatchAfterCurrentBus: true
));
```

| Option VO           | Fields                                                       |
|---------------------|--------------------------------------------------------------|
| `CommandBusOptions` | `int $delayMs = 0`, `?list<string> $validationGroups = null` |
| `QueryBusOptions`   | `?list<string> $validationGroups = null`                     |
| `EventBusOptions`   | `int $delayMs = 0`, `bool $dispatchAfterCurrentBus = false`  |

The matching `*BusOptionsMiddleware` translates these into native Messenger stamps (`DelayStamp`, `ValidationStamp`,
`DispatchAfterCurrentBusStamp`).

## Exceptions

You catch **interfaces** (in the contracts layer); the implementation throws concrete classes that implement them.

| Interface                                                               | Thrown when                                                               |
|-------------------------------------------------------------------------|---------------------------------------------------------------------------|
| `InvalidCommandData` / `InvalidQueryData`                               | Input passed validation but is still invalid for some reason              |
| `UnexpectedCommandPropertyValue` / `UnexpectedQueryPropertyValue`       | A validated property still holds an unexpected value                      |
| `InvalidCommandReturnConfiguration` / `InvalidQueryReturnConfiguration` | A result is expected but the message reached no synchronous handler       |
| `UnexpectedCommandResult` / `UnexpectedQueryResult`                     | The handler returned a value that does not match the declared result type |

```php
use Etel\CQRS\Query\Exception\UnexpectedQueryResult;

try {
    $user = $queryBus->query(query: new GetUserById(42));
} catch (UnexpectedQueryResult $e) {
    // the handler returned something other than the declared UserView
}
```

## Static analysis (PHPStan)

The package bundles a PHPStan extension that infers `query()` / `command()` return types from the result attributes:

| Call                                                                              | Inferred type    |
|-----------------------------------------------------------------------------------|------------------|
| `query(new GetUserById(1))` with `#[QueryResult(UserView::class)]`                | `UserView`       |
| `query(new FindUser(...))` with `#[QueryResult(UserView::class, nullable: true)]` | `UserView\|null` |
| `query($q, expectResult: UserView::class)`                                        | `UserView`       |
| `command(new CreateUser(...))` with `#[CommandResult(CreatedUser::class)]`        | `CreatedUser`    |
| `command($c)` (no attribute)                                                      | `null`           |

If you use [`phpstan/extension-installer`](https://github.com/phpstan/extension-installer) the extension is registered
automatically. Otherwise include it manually:

```neon
# phpstan.neon
includes:
    - vendor/etel/cqrs/extension.neon
```

> **PhpStorm note.** The extension drives PHPStan; it cannot drive PhpStorm's own type inference. The explicit
> `$expectResult: Foo::class` form is understood by PhpStorm natively; attribute-based inference is a PHPStan feature.

## Development

This project targets PHP 8.4.

```bash
# Run the test suite
vendor/bin/phpunit

# Static analysis (PHPStan, level 10)
vendor/bin/phpstan analyse

# Check / fix code style (PSR-12 + PHP 8.x rulesets, strict types)
vendor/bin/php-cs-fixer check --diff
vendor/bin/php-cs-fixer fix
```

## License

Released under the [MIT License](LICENSE).
