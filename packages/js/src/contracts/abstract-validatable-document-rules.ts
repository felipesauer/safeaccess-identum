import { AbstractValidatableDocument } from './abstract-validatable-document.js';

/**
 * Extended base for validators that dispatch to state-specific rules.
 *
 * Adds the {@link doRule} hook for resolving which rule implementation
 * should handle validation, based on external parameters (e.g., state code).
 *
 * @internal Extend this class to create rule-dispatched validators.
 *
 * @see {@link AbstractValidatableDocument} Parent base class.
 */
export abstract class AbstractValidatableDocumentRules extends AbstractValidatableDocument {
    protected abstract doRule(): this;
}
