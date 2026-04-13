import { describe, it, expect } from 'vitest';
import { CNPJValidation } from '../../src/assets/cnpj/cnpj-validation.js';
import { ValidationException } from '../../src/exceptions/validation-exception.js';

describe(CNPJValidation.name, () => {
    it('validates numeric CNPJ (masked and unmasked) as true', () => {
        expect(new CNPJValidation('84.773.274/0001-03').validate()).toBe(true);
        expect(new CNPJValidation('31.605.328/0001-65').validate()).toBe(true);
        expect(new CNPJValidation('52838678000141').validate()).toBe(true);
        expect(new CNPJValidation('40.670.922/0001-20').validate()).toBe(true);
    });

    it('validates alphanumeric CNPJ as true', () => {
        expect(new CNPJValidation('A0000000000032').validate()).toBe(true);
        expect(new CNPJValidation('a0.000.000/0000-32').validate()).toBe(true);
    });

    it('rejects CNPJ with wrong check digits (numeric and alphanumeric)', () => {
        expect(new CNPJValidation('46.543.423/0001-22').validate()).toBe(false);
        expect(new CNPJValidation('09387424300012').validate()).toBe(false);
        expect(new CNPJValidation('A0000000000033').validate()).toBe(false);
    });

    it('rejects CNPJ with wrong length', () => {
        expect(new CNPJValidation('9999999999999').validate()).toBe(false);
        expect(new CNPJValidation('123456789012345').validate()).toBe(false);
        expect(new CNPJValidation('').validate()).toBe(false);
    });

    it('rejects numeric CNPJ made of repeated digits', () => {
        expect(new CNPJValidation('00000000000000').validate()).toBe(false);
        expect(new CNPJValidation('11111111111111').validate()).toBe(false);
        expect(new CNPJValidation('22.222.222/2222-22').validate()).toBe(false);
    });

    it('rejects letters in DV positions (must be digits)', () => {
        expect(new CNPJValidation('A00000000000AA').validate()).toBe(false);
        expect(new CNPJValidation('0000000000000A').validate()).toBe(false);
    });

    it('ignores non-alphanumeric characters before validating', () => {
        expect(new CNPJValidation('84.773.274/0001-03').validate()).toBe(true);
        expect(new CNPJValidation('  84 773.274//0001-03 \n\t').validate()).toBe(true);
        expect(new CNPJValidation('84773274000103').validate()).toBe(true);
    });

    it('whitelist makes value valid regardless of domain logic', () => {
        const v = new CNPJValidation('46.543.423/0001-22').whitelist(['46.543.423/0001-22']);
        expect(v.validate()).toBe(true);
    });

    it('blacklist makes value invalid regardless of domain logic', () => {
        const v = new CNPJValidation('84.773.274/0001-03').blacklist(['84.773.274/0001-03']);
        expect(v.validate()).toBe(false);
    });

    it('whitelist takes precedence over blacklist when both contain the raw value', () => {
        const v = new CNPJValidation('84.773.274/0001-03')
            .whitelist(['84.773.274/0001-03'])
            .blacklist(['84.773.274/0001-03']);
        expect(v.validate()).toBe(true);
    });

    it('validateOrFail() returns true when valid', () => {
        expect(new CNPJValidation('52838678000141').validateOrFail()).toBe(true);
        expect(new CNPJValidation('A0000000000032').validateOrFail()).toBe(true);
    });

    it('validateOrFail() throws when invalid', () => {
        expect(() => new CNPJValidation('09387424300012').validateOrFail())
            .toThrow(ValidationException);
        expect(() => new CNPJValidation('09387424300012').validateOrFail())
            .toThrow('input invalid');
    });

    it('validateOrFail() respects whitelist and blacklist', () => {
        expect(new CNPJValidation('09387424300012').whitelist(['09387424300012']).validateOrFail()).toBe(true);
        expect(() => new CNPJValidation('84.773.274/0001-03').blacklist(['84.773.274/0001-03']).validateOrFail())
            .toThrow(ValidationException);
    });

    it('rejects alphanumeric CNPJ with invalid character in the body', () => {
        expect(new CNPJValidation('A@0000000000032').validate()).toBe(false);
        expect(new CNPJValidation('AA00#000000032').validate()).toBe(false);
    });

    it('handles rest1 == 0 edge case (dv1 forced to 0)', () => {
        expect(new CNPJValidation('00000000001406').validate()).toBe(true);
    });

    it('handles rest1 == 1 edge case (dv1 forced to 0)', () => {
        expect(new CNPJValidation('00000000000604').validate()).toBe(true);
    });

    it('handles rest2 == 0 edge case (dv2 forced to 0)', () => {
        expect(new CNPJValidation('00000000001910').validate()).toBe(true);
    });

    it('handles rest2 == 1 edge case (dv2 forced to 0)', () => {
        expect(new CNPJValidation('00000000001830').validate()).toBe(true);
    });

    it('validates CNPJ with Z in body (charCode boundary)', () => {
        expect(new CNPJValidation('Z0000000000001').validate()).toBe(true);
    });

    it('validates diverse CNPJs to kill weight mutations', () => {
        expect(new CNPJValidation('10000000000064').validate()).toBe(true);
        expect(new CNPJValidation('10000765432131').validate()).toBe(true);
        expect(new CNPJValidation('10001530864200').validate()).toBe(true);
        expect(new CNPJValidation('10002296296318').validate()).toBe(true);
        expect(new CNPJValidation('10003061728402').validate()).toBe(true);
        expect(new CNPJValidation('10003827160576').validate()).toBe(true);
    });

    it('handles rest1 == 2 edge case (dv1 forced to 9)', () => {
        expect(new CNPJValidation('00000000000191').validate()).toBe(true);
    });

    it('handles rest2 == 2 edge case (dv2 forced to 9)', () => {
        expect(new CNPJValidation('00000000000949').validate()).toBe(true);
    });

    it('rejects CNPJ with correct dv1 but wrong dv2', () => {
        expect(new CNPJValidation('84773274000102').validate()).toBe(false);
    });

    it('rejects CNPJ with wrong dv1 but correct dv2', () => {
        expect(new CNPJValidation('84773274000111').validate()).toBe(false);
    });

    it('rejects CNPJ with valid digits but extra length', () => {
        expect(new CNPJValidation('847732740001030').validate()).toBe(false);
    });
});
