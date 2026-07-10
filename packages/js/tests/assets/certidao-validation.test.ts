import { describe, it, expect } from 'vitest';
import { CertidaoValidation } from '../../src/assets/certidao/certidao-validation.js';
import { ValidationException } from '../../src/exceptions/validation-exception.js';

describe(CertidaoValidation.name, () => {
    it('validates the official CRC sample matrícula', () => {
        expect(new CertidaoValidation('00188301551987100018050000056665').isValid()).toBe(true);
    });

    it('accepts a matrícula with formatting punctuation', () => {
        expect(new CertidaoValidation('001883 01 55 1987 1 00018 050 0000566 65').isValid()).toBe(true);
    });

    it('rejects wrong length with wrong_length', () => {
        expect(new CertidaoValidation('123').validate().reason).toBe('wrong_length');
        expect(new CertidaoValidation('001883015519871000180500000566650').validate().reason).toBe('wrong_length');
    });

    it('rejects wrong check digits with bad_check_digit', () => {
        expect(new CertidaoValidation('00188301551987100018050000056600').validate().reason).toBe('bad_check_digit');
    });

    it('exposes the certificate kind via meta.type (book-type digit)', () => {
        expect(new CertidaoValidation('00188301551987100018050000056665').validate().meta?.type).toBe('birth');
    });

    it('validateOrFail() throws with the certidao document prefix', () => {
        expect(() => new CertidaoValidation('00188301551987100018050000056600').validateOrFail()).toThrow(
            ValidationException,
        );
        expect(() => new CertidaoValidation('00188301551987100018050000056600').validateOrFail()).toThrow(
            'certidao: bad_check_digit',
        );
    });
});
