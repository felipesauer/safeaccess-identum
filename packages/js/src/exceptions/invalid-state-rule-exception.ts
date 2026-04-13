import { ValidationException } from './validation-exception.js';

/**
 * Thrown when an invalid or unsupported state rule is requested.
 *
 * Raised when the provided state code does not match any registered
 * IE (Inscrição Estadual) validation rule.
 *
 * @see {@link ValidationException} Parent exception class.
 */
export class InvalidStateRuleException extends ValidationException {
    constructor(message: string) {
        super(message);
        this.name = 'InvalidStateRuleException';
    }
}
