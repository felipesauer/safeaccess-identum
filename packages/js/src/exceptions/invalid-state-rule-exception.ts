import { ReasonCode } from '../contracts/reason-code.js';
import { ValidationException } from './validation-exception.js';

/**
 * Thrown by {@link IEValidation} when the state code does not map to any
 * registered IE rule (i.e., not a valid IBGE UF code).
 *
 * Always carries {@link ReasonCode.UnknownUf}.
 *
 * @see {@link ValidationException}
 */
export class InvalidStateRuleException extends ValidationException {
    constructor(document = 'ie', normalized = '') {
        super(document, ReasonCode.UnknownUf, normalized);
        this.name = 'InvalidStateRuleException';
    }
}
