import { describe, it, expect } from 'vitest';
import { RenavamValidation } from '../../src/assets/renavam/renavam-validation.js';
import { ValidationException } from '../../src/exceptions/validation-exception.js';

describe(RenavamValidation.name, () => {
    it('validates RENAVAM (masked and unmasked) as true', () => {
        expect(new RenavamValidation('60390908553').validate()).toBe(true);
        expect(new RenavamValidation('34118026873').validate()).toBe(true);
        expect(new RenavamValidation('44666210669').validate()).toBe(true);
        expect(new RenavamValidation(' 6039 0908-553 ').validate()).toBe(true);
        expect(new RenavamValidation('34.118.026-873').validate()).toBe(true);
    });

    it('rejects wrong check digit (DV mismatch)', () => {
        expect(new RenavamValidation('60390908550').validate()).toBe(false);
        expect(new RenavamValidation('34118026870').validate()).toBe(false);
    });

    it('rejects wrong length and repeated sequences', () => {
        expect(new RenavamValidation('6039090855').validate()).toBe(false);
        expect(new RenavamValidation('603909085530').validate()).toBe(false);
        expect(new RenavamValidation('00000000000').validate()).toBe(false);
        expect(new RenavamValidation('11111111111').validate()).toBe(false);
    });

    it('ignores non-digit characters before validating', () => {
        expect(new RenavamValidation('341.180.268-73').validate()).toBe(true);
        expect(new RenavamValidation('  341 180..268-73 \n\t').validate()).toBe(true);
        expect(new RenavamValidation('34118026873').validate()).toBe(true);
    });

    it('supports whitelist and blacklist with validateOrFail', () => {
        expect(new RenavamValidation('12345678901').whitelist(['12345678901']).validateOrFail()).toBe(true);

        const bl = new RenavamValidation('60390908553').blacklist(['60390908553']);
        expect(bl.validate()).toBe(false);
        expect(() => bl.validateOrFail()).toThrow(ValidationException);
        expect(() => bl.validateOrFail()).toThrow('input invalid');
    });

    it('hits dv>=10 branch and coerces dv to 0', () => {
        expect(new RenavamValidation('00100000010').validate()).toBe(true);
    });

    it('validates diverse RENAVAMs to kill weight mutations', () => {
        expect(new RenavamValidation('10000000008').validate()).toBe(true);
        expect(new RenavamValidation('10876543210').validate()).toBe(true);
        expect(new RenavamValidation('11753086420').validate()).toBe(true);
        expect(new RenavamValidation('12629629637').validate()).toBe(true);
    });
});
