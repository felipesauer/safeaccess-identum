import { describe, it, expect } from 'vitest';
import { CPFValidation } from '../../src/assets/cpf/cpf-validation.js';
import { ValidationException } from '../../src/exceptions/validation-exception.js';

describe(CPFValidation.name, () => {
    it('validates CPF (masked and unmasked) as true', () => {
        expect(new CPFValidation('864.600.120-24').validate()).toBe(true);
        expect(new CPFValidation('71031347070').validate()).toBe(true);
        expect(new CPFValidation('93011581088').validate()).toBe(true);
        expect(new CPFValidation('745.508.470-69').validate()).toBe(true);
    });

    it('rejects CPF with wrong check digits (DV)', () => {
        expect(new CPFValidation('323.543.123-43').validate()).toBe(false);
        expect(new CPFValidation('98273487634').validate()).toBe(false);
    });

    it('rejects CPF with wrong length', () => {
        expect(new CPFValidation('9999999999').validate()).toBe(false);
        expect(new CPFValidation('123456789012').validate()).toBe(false);
    });

    it('rejects CPF made of repeated digits', () => {
        expect(new CPFValidation('00000000000').validate()).toBe(false);
        expect(new CPFValidation('11111111111').validate()).toBe(false);
        expect(new CPFValidation('222.222.222-22').validate()).toBe(false);
    });

    it('rejects empty or non-sense strings', () => {
        expect(new CPFValidation('').validate()).toBe(false);
        expect(new CPFValidation('   ').validate()).toBe(false);
    });

    it('exposes the raw value via raw()', () => {
        expect(new CPFValidation('864.600.120-24').raw()).toBe('864.600.120-24');
    });

    it('ignores non-digit characters before validating', () => {
        expect(new CPFValidation('864.600.120-24').validate()).toBe(true);
        expect(new CPFValidation('  864 600-120..24 \n\t').validate()).toBe(true);
        expect(new CPFValidation('86460012024').validate()).toBe(true);
    });

    it('handles rest1 == 0 edge case (dv1 forced to 0)', () => {
        expect(new CPFValidation('00000001406').validate()).toBe(true);
    });

    it('handles rest1 == 1 edge case (dv1 forced to 0)', () => {
        expect(new CPFValidation('00000000604').validate()).toBe(true);
    });

    it('handles rest2 == 0 edge case (dv2 forced to 0)', () => {
        expect(new CPFValidation('00000001910').validate()).toBe(true);
    });

    it('handles rest2 == 1 edge case (dv2 forced to 0)', () => {
        expect(new CPFValidation('00000001830').validate()).toBe(true);
    });

    it('validates diverse CPFs to kill weight mutations', () => {
        expect(new CPFValidation('10000000019').validate()).toBe(true);
        expect(new CPFValidation('10087654300').validate()).toBe(true);
        expect(new CPFValidation('10175308667').validate()).toBe(true);
        expect(new CPFValidation('10262962934').validate()).toBe(true);
        expect(new CPFValidation('10350617244').validate()).toBe(true);
        expect(new CPFValidation('10438271530').validate()).toBe(true);
    });

    it('handles rest1 == 2 edge case (dv1 forced to 9)', () => {
        expect(new CPFValidation('00000000191').validate()).toBe(true);
    });

    it('handles rest2 == 2 edge case (dv2 forced to 9)', () => {
        expect(new CPFValidation('00000000949').validate()).toBe(true);
    });

    it('rejects CPF with correct dv1 but wrong dv2', () => {
        expect(new CPFValidation('86460012023').validate()).toBe(false);
    });

    it('rejects CPF with wrong dv1 but correct dv2', () => {
        expect(new CPFValidation('86460012008').validate()).toBe(false);
    });

    it('rejects CPF with valid digits but extra length', () => {
        expect(new CPFValidation('864600120240').validate()).toBe(false);
    });

    it('whitelist() overrides invalid result', () => {
        expect(new CPFValidation('323.543.123-43').whitelist(['323.543.123-43']).validate()).toBe(true);
    });

    it('blacklist() overrides valid result', () => {
        expect(new CPFValidation('864.600.120-24').blacklist(['864.600.120-24']).validate()).toBe(false);
    });

    it('validateOrFail() returns true when valid', () => {
        expect(new CPFValidation('864.600.120-24').validateOrFail()).toBe(true);
    });

    it('validateOrFail() throws ValidationException when invalid', () => {
        expect(() => new CPFValidation('323.543.123-43').validateOrFail()).toThrow(ValidationException);
        expect(() => new CPFValidation('323.543.123-43').validateOrFail()).toThrow('input invalid');
    });

    it('validateOrFail() respects whitelist and blacklist', () => {
        expect(new CPFValidation('323.543.123-43').whitelist(['323.543.123-43']).validateOrFail()).toBe(true);
        expect(() => new CPFValidation('864.600.120-24').blacklist(['864.600.120-24']).validateOrFail()).toThrow(ValidationException);
    });
});
