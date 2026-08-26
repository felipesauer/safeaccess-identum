import { describe, it, expect } from 'vitest';
import { CEPValidation } from '../../src/assets/cep/cep-validation.js';
import { ValidationException } from '../../src/exceptions/validation-exception.js';

describe(CEPValidation.name, () => {
    it('accepts CEP masked and unmasked', () => {
        expect(new CEPValidation('78000-000').isValid()).toBe(true);
        expect(new CEPValidation('01310923').isValid()).toBe(true);
    });

    it('rejects wrong length or empty', () => {
        expect(new CEPValidation('78000-00').isValid()).toBe(false);
        expect(new CEPValidation('013109230').isValid()).toBe(false);
        expect(new CEPValidation('').isValid()).toBe(false);
    });

    it('ignores non-digits before validating', () => {
        expect(new CEPValidation('  78000-000 ').isValid()).toBe(true);
        expect(new CEPValidation('78000000').isValid()).toBe(true);
    });

    it('supports whitelist and blacklist short-circuits', () => {
        const wl = new CEPValidation('00000-000').allowList(['00000-000']);
        expect(wl.isValid()).toBe(true);
        expect(wl.validateOrFail()).toBeUndefined();

        const bl = new CEPValidation('78000-000').denyList(['78000-000']);
        expect(bl.isValid()).toBe(false);
        expect(() => bl.validateOrFail()).toThrow(ValidationException);
    });

    it('reports wrong_length and prefixes the reason with the document name', () => {
        expect(new CEPValidation('78000-00').validate().reason).toBe('wrong_length');
        expect(() => new CEPValidation('78000-00').validateOrFail()).toThrow('cep: wrong_length');
    });

    it('format() applies the 00000-000 mask and leaves ill-fitting values untouched', () => {
        expect(new CEPValidation('78000000').format()).toBe('78000-000');
        expect(new CEPValidation('7800000').format()).toBe('7800000');
        expect(new CEPValidation('780000000').format()).toBe('780000000');
    });

    it('generate() returns an unmasked CEP by default', () => {
        expect(CEPValidation.generate()).toMatch(/^\d{8}$/);
        expect(CEPValidation.generate(true)).toMatch(/^\d{5}-\d{3}$/);
    });
});
