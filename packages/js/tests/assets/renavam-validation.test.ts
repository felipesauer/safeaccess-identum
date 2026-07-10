import { describe, it, expect } from 'vitest';
import { RenavamValidation } from '../../src/assets/renavam/renavam-validation.js';
import { ValidationException } from '../../src/exceptions/validation-exception.js';

describe(RenavamValidation.name, () => {
    it('validates RENAVAM (masked and unmasked) as true', () => {
        expect(new RenavamValidation('60390908553').isValid()).toBe(true);
        expect(new RenavamValidation('34118026873').isValid()).toBe(true);
        expect(new RenavamValidation('44666210669').isValid()).toBe(true);
        expect(new RenavamValidation(' 6039 0908-553 ').isValid()).toBe(true);
        expect(new RenavamValidation('34.118.026-873').isValid()).toBe(true);
    });

    it('rejects wrong check digit (DV mismatch)', () => {
        expect(new RenavamValidation('60390908550').isValid()).toBe(false);
        expect(new RenavamValidation('34118026870').isValid()).toBe(false);
    });

    it('rejects wrong length and repeated sequences', () => {
        expect(new RenavamValidation('6039090855').isValid()).toBe(false);
        expect(new RenavamValidation('603909085530').isValid()).toBe(false);
        expect(new RenavamValidation('00000000000').isValid()).toBe(false);
        expect(new RenavamValidation('11111111111').isValid()).toBe(false);
    });

    it('ignores non-digit characters before validating', () => {
        expect(new RenavamValidation('341.180.268-73').isValid()).toBe(true);
        expect(new RenavamValidation('  341 180..268-73 \n\t').isValid()).toBe(true);
        expect(new RenavamValidation('34118026873').isValid()).toBe(true);
    });

    it('supports whitelist and blacklist with validateOrFail', () => {
        expect(new RenavamValidation('12345678901').allowList(['12345678901']).validateOrFail()).toBeUndefined();

        const bl = new RenavamValidation('60390908553').denyList(['60390908553']);
        expect(bl.isValid()).toBe(false);
        expect(() => bl.validateOrFail()).toThrow(ValidationException);
        expect(() => bl.validateOrFail()).toThrow('renavam: denied');
    });

    it('hits dv>=10 branch and coerces dv to 0', () => {
        expect(new RenavamValidation('00100000010').isValid()).toBe(true);
    });

    it('validates diverse RENAVAMs to kill weight mutations', () => {
        expect(new RenavamValidation('10000000008').isValid()).toBe(true);
        expect(new RenavamValidation('10876543210').isValid()).toBe(true);
        expect(new RenavamValidation('11753086420').isValid()).toBe(true);
        expect(new RenavamValidation('12629629637').isValid()).toBe(true);
    });
});
