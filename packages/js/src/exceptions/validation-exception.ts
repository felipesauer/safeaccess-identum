/**
 * Root exception for all validation errors. Catch this to handle any
 * failure coming out of the library without importing every subclass.
 *
 * @see {@link InvalidStateRuleException}
 */
export class ValidationException extends Error {
    constructor(message: string) {
        super(message);
        this.name = 'ValidationException';
    }
}
