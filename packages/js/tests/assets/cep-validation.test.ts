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
});
