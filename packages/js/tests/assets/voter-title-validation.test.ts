import { describe, it, expect } from 'vitest';
import { VoterTitleValidation } from '../../src/assets/voter/voter-title-validation.js';
import { ValidationException } from '../../src/exceptions/validation-exception.js';

describe(VoterTitleValidation.name, () => {
    it('validates Voter Title (masked and unmasked) as true', () => {
        expect(new VoterTitleValidation('123456781295').isValid()).toBe(true);
        expect(new VoterTitleValidation('314159261260').isValid()).toBe(true);
        expect(new VoterTitleValidation('  1234 5678 12-95 ').isValid()).toBe(true);
        expect(new VoterTitleValidation('3141.5926.12-60').isValid()).toBe(true);
    });

    it('rejects wrong check digits (DV mismatch)', () => {
        expect(new VoterTitleValidation('123456781294').isValid()).toBe(false);
        expect(new VoterTitleValidation('314159261261').isValid()).toBe(false);
    });

    it('rejects wrong length and repeated sequences', () => {
        expect(new VoterTitleValidation('12345678129').isValid()).toBe(false);
        expect(new VoterTitleValidation('1234567812950').isValid()).toBe(false);
        expect(new VoterTitleValidation('000000000000').isValid()).toBe(false);
        expect(new VoterTitleValidation('111111111111').isValid()).toBe(false);
    });

    it('whitelist short-circuits to valid and blacklist to invalid', () => {
        expect(new VoterTitleValidation('123456781294').allowList(['123456781294']).validateOrFail()).toBeUndefined();

        const bl = new VoterTitleValidation('123456781295').denyList(['123456781295']);
        expect(bl.isValid()).toBe(false);
        expect(() => bl.validateOrFail()).toThrow(ValidationException);
        expect(() => bl.validateOrFail()).toThrow('voter-title: denied');
    });

    it('hits the dv==10 -> 0 edge in dv1 and dv2', () => {
        expect(new VoterTitleValidation('000000060400').isValid()).toBe(true);
    });

    it('validates voter titles from diverse UF codes to kill weight mutations', () => {
        expect(new VoterTitleValidation('100000000124').isValid()).toBe(true);
        expect(new VoterTitleValidation('100000001520').isValid()).toBe(true);
        expect(new VoterTitleValidation('100000002720').isValid()).toBe(true);
        expect(new VoterTitleValidation('100000003522').isValid()).toBe(true);
        expect(new VoterTitleValidation('100000005029').isValid()).toBe(true);
    });

    it('rejects voter title with correct dv1 but wrong dv2', () => {
        expect(new VoterTitleValidation('123456781290').isValid()).toBe(false);
    });

    it('rejects voter title with wrong dv1 but correct dv2', () => {
        expect(new VoterTitleValidation('100000000037').isValid()).toBe(false);
    });

    it('rejects voter title with valid digits but extra length', () => {
        expect(new VoterTitleValidation('1000000000270').isValid()).toBe(false);
    });
});
