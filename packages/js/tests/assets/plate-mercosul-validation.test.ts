import { describe, it, expect } from 'vitest';
import { PlateMercosulValidation } from '../../src/assets/plate/plate-mercosul-validation.js';
import { ValidationException } from '../../src/exceptions/validation-exception.js';

describe(PlateMercosulValidation.name, () => {
    it('validates Mercosul plate masked and unmasked', () => {
        expect(new PlateMercosulValidation('BRA1A23').validate()).toBe(true);
        expect(new PlateMercosulValidation('bra-1a23').validate()).toBe(true);
        expect(new PlateMercosulValidation('ABC3D45').validate()).toBe(true);
    });

    it('rejects wrong formats and lengths', () => {
        expect(new PlateMercosulValidation('ABC-1234').validate()).toBe(false);
        expect(new PlateMercosulValidation('AB1CD23').validate()).toBe(false);
        expect(new PlateMercosulValidation('ABCD123').validate()).toBe(false);
        expect(new PlateMercosulValidation('BRA1A2').validate()).toBe(false);
        expect(new PlateMercosulValidation('BRA1A234').validate()).toBe(false);
    });

    it('supports whitelist and blacklist', () => {
        expect(new PlateMercosulValidation('ABC1D23').whitelist(['ABC1D23']).validate()).toBe(true);

        const bl = new PlateMercosulValidation('BRA1A23').blacklist(['BRA1A23']);
        expect(bl.validate()).toBe(false);
        expect(() => bl.validateOrFail()).toThrow(ValidationException);
    });

    it('rejects all-digit 7-char string (correct length, wrong format)', () => {
        expect(new PlateMercosulValidation('1234567').validate()).toBe(false);
    });

    it('validates minimum boundary plate format', () => {
        expect(new PlateMercosulValidation('AAA0A00').validate()).toBe(true);
        expect(new PlateMercosulValidation('ZZZ9Z99').validate()).toBe(true);
    });

    it('rejects empty string', () => {
        expect(new PlateMercosulValidation('').validate()).toBe(false);
    });
});
