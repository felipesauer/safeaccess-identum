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

    it('rejects a wrong D1 even when D2 is consistent with it', () => {
        // D1 is 7 where 6 is expected; D2 was recomputed over the corrupted D1.
        expect(new CertidaoValidation('00188301551987100018050000056673').validate().reason).toBe(
            'bad_check_digit',
        );
    });

    it('rejects a wrong D2 when D1 is correct', () => {
        expect(new CertidaoValidation('00188301551987100018050000056666').validate().reason).toBe(
            'bad_check_digit',
        );
    });

    it('maps a Mod-11 remainder of 10 to a check digit of 1', () => {
        // D1 raw remainder is 10, so the expected digit is 1.
        expect(new CertidaoValidation('00000101551987100018050000056615').isValid()).toBe(true);
        // Same for D2.
        expect(new CertidaoValidation('00000501551987100018050000056651').isValid()).toBe(true);
    });

    it('maps every book-type digit to its certificate kind', () => {
        const kinds: Array<[string, string | null]> = [
            ['00188301551987100018050000056665', 'birth'],
            ['00188301551987200018050000056601', 'marriage'],
            ['00188301551987300018050000056654', 'marriage'],
            ['00188301551987400018050000056615', 'death'],
            ['00188301551987500018050000056643', 'stillbirth'],
            ['00188301551987700018050000056632', 'other'],
            ['00188301551987600018050000056698', null], // unmapped book type
        ];

        for (const [matricula, kind] of kinds) {
            const result = new CertidaoValidation(matricula).validate();
            expect(result.valid).toBe(true);
            expect(result.meta?.type).toBe(kind);
        }
    });
});
