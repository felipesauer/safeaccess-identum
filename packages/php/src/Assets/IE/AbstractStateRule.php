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
     * Execute the validation for the given IE string.
     *
     * Implementations should:
     * - Normalize the input (digits-only or alphanumeric as required by the UF).
     * - Apply length checks, repeated-sequence checks, and check digit algorithms.
     * - Return true if the IE is valid for the specific UF, false otherwise.
     *
     * @param string $ie Raw IE value (may include formatting or separators).
     * @return bool True if valid according to the UF's rule, false otherwise.
     */
    abstract public function execute(string $ie): bool;
}
