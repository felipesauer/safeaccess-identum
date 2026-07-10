import { describe, it, expect } from 'vitest';
import { PlateMercosulValidation } from '../../src/assets/plate/plate-mercosul-validation.js';
import { ValidationException } from '../../src/exceptions/validation-exception.js';

describe(PlateMercosulValidation.name, () => {
    it('validates Mercosul plate masked and unmasked', () => {
        expect(new PlateMercosulValidation('BRA1A23').isValid()).toBe(true);
        expect(new PlateMercosulValidation('bra-1a23').isValid()).toBe(true);
        expect(new PlateMercosulValidation('ABC3D45').isValid()).toBe(true);
    });

    it('rejects wrong formats and lengths', () => {
        expect(new PlateMercosulValidation('ABC-1234').isValid()).toBe(false);
        expect(new PlateMercosulValidation('AB1CD23').isValid()).toBe(false);
        expect(new PlateMercosulValidation('ABCD123').isValid()).toBe(false);
        expect(new PlateMercosulValidation('BRA1A2').isValid()).toBe(false);
        expect(new PlateMercosulValidation('BRA1A234').isValid()).toBe(false);
    });

    it('supports whitelist and blacklist', () => {
        expect(new PlateMercosulValidation('ABC1D23').allowList(['ABC1D23']).isValid()).toBe(true);

        const bl = new PlateMercosulValidation('BRA1A23').denyList(['BRA1A23']);
        expect(bl.isValid()).toBe(false);
        expect(() => bl.validateOrFail()).toThrow(ValidationException);
    });

    it('rejects all-digit 7-char string (correct length, wrong format)', () => {
        expect(new PlateMercosulValidation('1234567').isValid()).toBe(false);
    });

    it('validates minimum boundary plate format', () => {
        expect(new PlateMercosulValidation('AAA0A00').isValid()).toBe(true);
        expect(new PlateMercosulValidation('ZZZ9Z99').isValid()).toBe(true);
    });

    it('rejects empty string', () => {
        expect(new PlateMercosulValidation('').isValid()).toBe(false);
    });
});
