import { ValidationException } from './validation-exception.js';

/**
 * Thrown by {@link IEValidation} when the state code does not map to any
 * registered IE rule (i.e., not a valid IBGE UF code).
 *
 * @see {@link ValidationException}
 */
export class InvalidStateRuleException extends ValidationException {
    constructor(message: string) {
        super(message);
        this.name = 'InvalidStateRuleException';
    }
}
