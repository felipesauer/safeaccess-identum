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

    it('distinguishes wrong_length from invalid_format', () => {
        expect(new PlateMercosulValidation('BRA1A2').validate().reason).toBe('wrong_length');
        expect(new PlateMercosulValidation('BRA1A234').validate().reason).toBe('wrong_length');
        expect(new PlateMercosulValidation('ABC1234').validate().reason).toBe('invalid_format');
        expect(new PlateMercosulValidation('1234567').validate().reason).toBe('invalid_format');
    });

    it('prefixes the thrown reason with the document name', () => {
        expect(() => new PlateMercosulValidation('ABC1234').validateOrFail()).toThrow(
            'plate: invalid_format',
        );
    });

    it('exposes the layout via meta.pattern', () => {
        expect(new PlateMercosulValidation('BRA1A23').validate().meta?.pattern).toBe('mercosul');
    });

    it('trims surrounding whitespace before validating', () => {
        expect(new PlateMercosulValidation('   BRA1A23   ').isValid()).toBe(true);
        expect(new PlateMercosulValidation('\tBRA1A23\n').strip()).toBe('BRA1A23');
    });

    it('generate() produces the LLLNLNN layout', () => {
        for (let i = 0; i < 50; i++) {
            const plate = PlateMercosulValidation.generate();
            expect(plate).toMatch(/^[A-Z]{3}[0-9][A-Z][0-9]{2}$/);
            expect(new PlateMercosulValidation(plate).isValid()).toBe(true);
        }
    });
});
