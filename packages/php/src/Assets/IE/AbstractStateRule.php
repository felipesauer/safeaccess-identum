<?php

declare(strict_types=1);

namespace SafeAccess\Identum\Assets\IE;

use SafeAccess\Identum\Internal\Makeable;

/**
 * Base class for per-state IE validation rules.
 *
 * Each Brazilian state has its own IE format and check-digit algorithm.
 * Subclasses implement {@see execute()} with the state-specific logic.
 *
 * @internal
 *
 * @see IEValidation Validator that dispatches to concrete state rules.
 * @see StateEnum    Enum mapping state codes to rule implementations.
 */
abstract class AbstractStateRule
{
    use Makeable;
    use DocumentMath;

    /**
     * Validate using the UF-specific algorithm.
     *
     * @param string $ie Raw IE value (may include formatting).
     * @return bool
     */
    abstract public function execute(string $ie): bool;
}
