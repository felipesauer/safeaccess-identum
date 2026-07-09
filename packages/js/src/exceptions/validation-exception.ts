import type { ReasonCode } from '../contracts/reason-code.js';

/**
 * Root exception for all validation errors. Catch this to handle any
 * failure coming out of the library without importing every subclass.
 *
 * Carries structured context — the document type, the machine-readable
 * {@link ReasonCode}, and the normalized input — so callers can branch
 * programmatically instead of parsing the message string.
 *
 * @see {@link InvalidStateRuleException}
 */
export class ValidationException extends Error {
    readonly document: string;
    readonly reason: ReasonCode;
    readonly normalized: string;

    constructor(document: string, reason: ReasonCode, normalized: string) {
        super(`${document}: ${reason}`);
        this.name = 'ValidationException';
        this.document = document;
        this.reason = reason;
        this.normalized = normalized;
    }
}
