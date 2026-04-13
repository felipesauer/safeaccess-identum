<?php

declare(strict_types=1);

namespace SafeAccess\Identum\Contracts;

/**
 * Extended base for validators that dispatch to state-specific rules.
 *
 * Adds the {@see doRule()} hook for resolving which rule implementation
 * should handle validation, based on external parameters (e.g., state code).
 *
 * @internal Extend this class to create rule-dispatched validators.
 *
 * @see AbstractValidatableDocument Parent base class.
 * @see \SafeAccess\Identum\Assets\IE\IEValidation Concrete implementation using this base.
 */
abstract class AbstractValidatableDocumentRules extends AbstractValidatableDocument
{
    /**
     * @return static
     */
    abstract protected function doRule(): static;
}
