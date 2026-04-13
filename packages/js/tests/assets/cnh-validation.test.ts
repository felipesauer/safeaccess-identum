import { describe, it, expect } from 'vitest';
import { CNHValidation } from '../../src/assets/cnh/cnh-validation.js';
import { ValidationException } from '../../src/exceptions/validation-exception.js';

describe(CNHValidation.name, () => {
    it('validates CNH numbers as true (unmasked and with noise)', () => {
        expect(new CNHValidation('22522791508').validate()).toBe(true);
        expect(new CNHValidation('12345678900').validate()).toBe(true);
        expect(new CNHValidation('  225227915-08 ').validate()).toBe(true);
    });

    it('rejects wrong check digits (DV mismatch)', () => {
        expect(new CNHValidation('22522791509').validate()).toBe(false);
        expect(new CNHValidation('92079525000').validate()).toBe(false);
    });

    it('rejects wrong lengths and repeated sequences', () => {
        expect(new CNHValidation('2252279150').validate()).toBe(false);
        expect(new CNHValidation('225227915080').validate()).toBe(false);
        expect(new CNHValidation('00000000000').validate()).toBe(false);
        expect(new CNHValidation('11111111111').validate()).toBe(false);
    });

    it('whitelist short-circuits to valid even if DVs are wrong', () => {
        const v = new CNHValidation('99999999999').whitelist(['99999999999']);
        expect(v.validate()).toBe(true);
        expect(v.validateOrFail()).toBe(true);
    });

    it('blacklist short-circuits to invalid even if domain is valid', () => {
        const v = new CNHValidation('22522791508').blacklist(['22522791508']);
        expect(v.validate()).toBe(false);
        expect(() => v.validateOrFail()).toThrow(ValidationException);
        expect(() => v.validateOrFail()).toThrow('input invalid');
    });

    it('validateOrFail returns true for valid and throws for invalid', () => {
        expect(new CNHValidation('12345678900').validateOrFail()).toBe(true);
        expect(() => new CNHValidation('92079525000').validateOrFail()).toThrow(ValidationException);
        expect(() => new CNHValidation('92079525000').validateOrFail()).toThrow('input invalid');
    });

    it('hits the dv2 adjustment branch (dv1=10→0 and dv2-2<0 → +9)', () => {
        expect(new CNHValidation('10005500000').validate()).toBe(true);
    });

    it('hits dv2 -= 2 branch (firstIsTenPlus=true and dv2-2 >= 0)', () => {
        expect(new CNHValidation('10000003600').validate()).toBe(true);
    });

    it('hits dv2 > 9 clamp in normal path (sum2%11 == 10)', () => {
        expect(new CNHValidation('10000001550').validate()).toBe(true);
    });

    it('validates CNH where dv1 == 9 (boundary, not clamped)', () => {
        expect(new CNHValidation('00000000994').validate()).toBe(true);
    });

    it('validates CNH where dv2 == 9 without firstIsTenPlus (boundary, not clamped)', () => {
        expect(new CNHValidation('90000000049').validate()).toBe(true);
    });

    it('rejects CNH with wrong dv1 but correct dv2', () => {
        expect(new CNHValidation('22522791518').validate()).toBe(false);
    });

    it('rejects CNH with valid digits but extra length', () => {
        expect(new CNHValidation('225227915080').validate()).toBe(false);
    });
});
