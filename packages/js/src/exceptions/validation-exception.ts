/**
 * Base exception for all SafeAccess Identum errors.
 *
 * Serves as the root of the exception hierarchy, enabling catch-all handling
 * for any error originating from document validation operations.
 *
 * @see {@link InvalidStateRuleException} Thrown when an invalid state rule is requested.
 */
export class ValidationException extends Error {
    constructor(message: string) {
        super(message);
        this.name = 'ValidationException';
    }
}
