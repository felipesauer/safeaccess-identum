import { describe, it, expect } from 'vitest';
import { CEPValidation } from '../../src/assets/cep/cep-validation.js';
import { ValidationException } from '../../src/exceptions/validation-exception.js';

describe(CEPValidation.name, () => {
    it('accepts CEP masked and unmasked', () => {
        expect(new CEPValidation('78000-000').validate()).toBe(true);
        expect(new CEPValidation('01310923').validate()).toBe(true);
    });

    it('rejects wrong length or empty', () => {
        expect(new CEPValidation('78000-00').validate()).toBe(false);
        expect(new CEPValidation('013109230').validate()).toBe(false);
        expect(new CEPValidation('').validate()).toBe(false);
    });

    it('ignores non-digits before validating', () => {
        expect(new CEPValidation('  78000-000 ').validate()).toBe(true);
        expect(new CEPValidation('78000000').validate()).toBe(true);
    });

    it('supports whitelist and blacklist short-circuits', () => {
        const wl = new CEPValidation('00000-000').whitelist(['00000-000']);
        expect(wl.validate()).toBe(true);
        expect(wl.validateOrFail()).toBe(true);

        const bl = new CEPValidation('78000-000').blacklist(['78000-000']);
        expect(bl.validate()).toBe(false);
        expect(() => bl.validateOrFail()).toThrow(ValidationException);
    });
});
