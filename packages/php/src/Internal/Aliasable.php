<?php

declare(strict_types=1);

namespace SafeAccess\Identum\Internal;

/**
 * Provides alias registration and dynamic static resolution.
 *
 * Enables dynamic dispatch via `__callStatic()`, mapping string aliases
 * to class names and instantiating them.
 *
 * @internal
 */
trait Aliasable
{
    use Makeable;

    /**
     * Alias registry for the consuming class.
     *
     * @var array<string, class-string>
     */
    protected array $alias = [];

    /**
     * Singleton-like instance registry keyed by consuming class.
     *
     * @var array<class-string, static>
     */
    protected static array $instances = [];

    /**
     * Boot the singleton-like instance for the consuming class if needed.
     *
     * @return void
     */
    public static function boot(): void
    {
        $class = static::class;

        if (!isset(self::$instances[$class])) {
            // use make() to allow future constructor parameters (via defaults)
            self::$instances[$class] = static::make();
        }
    }

    /**
     * Register an alias -> class-string mapping.
     *
     * The most recently registered alias takes precedence (prepended merge).
     *
     * @param string $alias Alias name.
     * @param class-string $class Fully-qualified target class name.
     * @return static Returns the stored instance (handy for chaining).
     *
     * @psalm-api
     */
    public static function alias(string $alias, string $class): static
    {
        static::boot();

        $self = self::$instances[static::class];
        $self->alias = [$alias => $class] + $self->alias;

        return $self;
    }

    /**
     * Dynamic static resolver.
     *
     * Example: MyRegistry::foo($a, $b) → resolves alias "foo" to a class
     * and instantiates it with `make(...)` if it uses Makeable, otherwise `new ...`.
     *
     * @param string $name
     * @param array<int, mixed> $arguments
     * @return mixed
     *
     * @throws \RuntimeException When the alias does not exist.
     *
     * @psalm-api
     */
    public static function __callStatic(string $name, array $arguments): mixed
    {
        static::boot();

        $aliases = self::$instances[static::class]->alias;

        if (!array_key_exists($name, $aliases)) {
            throw new \RuntimeException("Alias {$name} is not defined for class " . static::class);
        }

        /** @var class-string $targetClass */
        $targetClass = $aliases[$name];

        // Check on the TARGET class (not the caller) whether it uses Makeable
        $uses = class_uses($targetClass) ?: [];

        if (in_array(Makeable::class, $uses, true)) {
            return $targetClass::make(...$arguments);
        }

        return new $targetClass(...$arguments);
    }

    /**
     * Retrieve a specific alias mapping or all registered aliases.
     *
     * @param string|null $name Optional alias name. If empty, returns the full map.
     * @return string|array<string, class-string>|false Class name, full map, or false when not found.
     *
     * @psalm-api
     */
    public static function getAlias(?string $name = ''): string|array|false
    {
        static::boot();

        $aliases = self::$instances[static::class]->alias;

        return $name !== '' ? ($aliases[$name] ?? false) : $aliases;
    }
}
