import { describe, it, expect } from 'vitest';
import { VoterTitleValidation } from '../../src/assets/voter/voter-title-validation.js';
import { ValidationException } from '../../src/exceptions/validation-exception.js';

describe(VoterTitleValidation.name, () => {
    it('validates Voter Title (masked and unmasked) as true', () => {
        expect(new VoterTitleValidation('123456781295').validate()).toBe(true);
        expect(new VoterTitleValidation('314159261260').validate()).toBe(true);
        expect(new VoterTitleValidation('  1234 5678 12-95 ').validate()).toBe(true);
        expect(new VoterTitleValidation('3141.5926.12-60').validate()).toBe(true);
    });

    it('rejects wrong check digits (DV mismatch)', () => {
        expect(new VoterTitleValidation('123456781294').validate()).toBe(false);
        expect(new VoterTitleValidation('314159261261').validate()).toBe(false);
    });

    it('rejects wrong length and repeated sequences', () => {
        expect(new VoterTitleValidation('12345678129').validate()).toBe(false);
        expect(new VoterTitleValidation('1234567812950').validate()).toBe(false);
        expect(new VoterTitleValidation('000000000000').validate()).toBe(false);
        expect(new VoterTitleValidation('111111111111').validate()).toBe(false);
    });

    it('whitelist short-circuits to valid and blacklist to invalid', () => {
        expect(new VoterTitleValidation('123456781294').whitelist(['123456781294']).validateOrFail()).toBe(true);

        const bl = new VoterTitleValidation('123456781295').blacklist(['123456781295']);
        expect(bl.validate()).toBe(false);
        expect(() => bl.validateOrFail()).toThrow(ValidationException);
        expect(() => bl.validateOrFail()).toThrow('input invalid');
    });

    it('hits the dv==10 -> 0 edge in dv1 and dv2', () => {
        expect(new VoterTitleValidation('000000060400').validate()).toBe(true);
    });

    it('validates voter titles from diverse UF codes to kill weight mutations', () => {
        expect(new VoterTitleValidation('100000000124').validate()).toBe(true);
        expect(new VoterTitleValidation('100000001520').validate()).toBe(true);
        expect(new VoterTitleValidation('100000002720').validate()).toBe(true);
        expect(new VoterTitleValidation('100000003522').validate()).toBe(true);
        expect(new VoterTitleValidation('100000005029').validate()).toBe(true);
    });

    it('rejects voter title with correct dv1 but wrong dv2', () => {
        expect(new VoterTitleValidation('123456781290').validate()).toBe(false);
    });

    it('rejects voter title with wrong dv1 but correct dv2', () => {
        expect(new VoterTitleValidation('100000000037').validate()).toBe(false);
    });

    it('rejects voter title with valid digits but extra length', () => {
        expect(new VoterTitleValidation('1000000000270').validate()).toBe(false);
    });
});
