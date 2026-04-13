import { describe, it, expect } from 'vitest';
import { PISValidation } from '../../src/assets/pis/pis-validation.js';
import { ValidationException } from '../../src/exceptions/validation-exception.js';

describe(PISValidation.name, () => {
    it('validates PIS (masked and unmasked) as true', () => {
        expect(new PISValidation('32995061589').validate()).toBe(true);
        expect(new PISValidation('329.9506.158-9').validate()).toBe(true);
        expect(new PISValidation('19121693121').validate()).toBe(true);
        expect(new PISValidation('191.2169.312-1').validate()).toBe(true);
    });

    it('hits the dv==10 and dv==11 edge cases (dv coerced to 0)', () => {
        expect(new PISValidation('40000000000').validate()).toBe(true);
        expect(new PISValidation('76000000000').validate()).toBe(true);
    });

    it('rejects PIS with wrong DV or length or repeated digits', () => {
        expect(new PISValidation('32995061580').validate()).toBe(false);
        expect(new PISValidation('19121693120').validate()).toBe(false);
        expect(new PISValidation('3299506158').validate()).toBe(false);
        expect(new PISValidation('329950615890').validate()).toBe(false);
        expect(new PISValidation('00000000000').validate()).toBe(false);
        expect(new PISValidation('11111111111').validate()).toBe(false);
    });

    it('ignores non-digits before validating', () => {
        expect(new PISValidation('329.9506.158-9').validate()).toBe(true);
        expect(new PISValidation('  329 9506..158-9 \n\t').validate()).toBe(true);
        expect(new PISValidation('32995061589').validate()).toBe(true);
    });

    it('supports whitelist and blacklist with validateOrFail', () => {
        expect(new PISValidation('12345678901').whitelist(['12345678901']).validateOrFail()).toBe(true);

        const bl = new PISValidation('19121693121').blacklist(['19121693121']);
        expect(bl.validate()).toBe(false);
        expect(() => bl.validateOrFail()).toThrow(ValidationException);
        expect(() => bl.validateOrFail()).toThrow('input invalid');
    });

    it('validates diverse PIS numbers to kill weight mutations', () => {
        expect(new PISValidation('10000000008').validate()).toBe(true);
        expect(new PISValidation('10008765437').validate()).toBe(true);
        expect(new PISValidation('10017530862').validate()).toBe(true);
        expect(new PISValidation('10026296290').validate()).toBe(true);
    });
});
