<?php

declare(strict_types=1);

namespace SafeAccess\Identum\Internal;

/**
 * Base class for resolvers with alias registration and resolution.
 *
 * Combines the {@see Aliasable} trait for dynamic alias dispatch with
 * the {@see Makeable} trait for consistent instantiation.
 *
 * @internal
 *
 * @see \SafeAccess\Identum\Identum Concrete resolver implementation.
 */
abstract class AbstractResolver
{
    use Aliasable;
    use Makeable;

    /**
     * Constructor.
     *
     * Accepts optional alias mappings and registers this instance
     * in the static registry for the current resolver class.
     *
     * @param array<string, class-string>|null $alias Optional alias mappings.
     */
    public function __construct(?array $alias = [])
    {
        if (!empty($alias)) {
            $this->alias = array_merge($this->alias, $alias);
        }

        static::$instances[static::class] = $this;
    }
}
