import { describe, it, expect } from 'vitest';
import { CPFValidation } from '../../src/assets/cpf/cpf-validation.js';
import { ValidationException } from '../../src/exceptions/validation-exception.js';

describe(CPFValidation.name, () => {
    it('validates CPF (masked and unmasked) as true', () => {
        expect(new CPFValidation('864.600.120-24').isValid()).toBe(true);
        expect(new CPFValidation('71031347070').isValid()).toBe(true);
        expect(new CPFValidation('93011581088').isValid()).toBe(true);
        expect(new CPFValidation('745.508.470-69').isValid()).toBe(true);
    });

    it('rejects CPF with wrong check digits (DV)', () => {
        expect(new CPFValidation('323.543.123-43').isValid()).toBe(false);
        expect(new CPFValidation('98273487634').isValid()).toBe(false);
    });

    it('rejects CPF with wrong length', () => {
        expect(new CPFValidation('9999999999').isValid()).toBe(false);
        expect(new CPFValidation('123456789012').isValid()).toBe(false);
    });

    it('rejects CPF made of repeated digits', () => {
        expect(new CPFValidation('00000000000').isValid()).toBe(false);
        expect(new CPFValidation('11111111111').isValid()).toBe(false);
        expect(new CPFValidation('222.222.222-22').isValid()).toBe(false);
    });

    it('rejects empty or non-sense strings', () => {
        expect(new CPFValidation('').isValid()).toBe(false);
        expect(new CPFValidation('   ').isValid()).toBe(false);
    });

    it('exposes the raw value via raw()', () => {
        expect(new CPFValidation('864.600.120-24').raw()).toBe('864.600.120-24');
    });

    it('ignores non-digit characters before validating', () => {
        expect(new CPFValidation('864.600.120-24').isValid()).toBe(true);
        expect(new CPFValidation('  864 600-120..24 \n\t').isValid()).toBe(true);
        expect(new CPFValidation('86460012024').isValid()).toBe(true);
    });

    it('handles rest1 == 0 edge case (dv1 forced to 0)', () => {
        expect(new CPFValidation('00000001406').isValid()).toBe(true);
    });

    it('handles rest1 == 1 edge case (dv1 forced to 0)', () => {
        expect(new CPFValidation('00000000604').isValid()).toBe(true);
    });

    it('handles rest2 == 0 edge case (dv2 forced to 0)', () => {
        expect(new CPFValidation('00000001910').isValid()).toBe(true);
    });

    it('handles rest2 == 1 edge case (dv2 forced to 0)', () => {
        expect(new CPFValidation('00000001830').isValid()).toBe(true);
    });

    it('validates diverse CPFs to kill weight mutations', () => {
        expect(new CPFValidation('10000000019').isValid()).toBe(true);
        expect(new CPFValidation('10087654300').isValid()).toBe(true);
        expect(new CPFValidation('10175308667').isValid()).toBe(true);
        expect(new CPFValidation('10262962934').isValid()).toBe(true);
        expect(new CPFValidation('10350617244').isValid()).toBe(true);
        expect(new CPFValidation('10438271530').isValid()).toBe(true);
    });

    it('handles rest1 == 2 edge case (dv1 forced to 9)', () => {
        expect(new CPFValidation('00000000191').isValid()).toBe(true);
    });

    it('handles rest2 == 2 edge case (dv2 forced to 9)', () => {
        expect(new CPFValidation('00000000949').isValid()).toBe(true);
    });

    it('rejects CPF with correct dv1 but wrong dv2', () => {
        expect(new CPFValidation('86460012023').isValid()).toBe(false);
    });

    it('rejects CPF with wrong dv1 but correct dv2', () => {
        expect(new CPFValidation('86460012008').isValid()).toBe(false);
    });

    it('rejects CPF with valid digits but extra length', () => {
        expect(new CPFValidation('864600120240').isValid()).toBe(false);
    });

    it('whitelist() overrides invalid result', () => {
        expect(new CPFValidation('323.543.123-43').allowList(['323.543.123-43']).isValid()).toBe(true);
    });

    it('whitelist()/blacklist() match regardless of formatting (both sides sanitized)', () => {
        // masked input vs unmasked list entry
        expect(new CPFValidation('323.543.123-43').allowList(['32354312343']).isValid()).toBe(true);
        // unmasked input vs masked list entry
        expect(new CPFValidation('86460012024').denyList(['864.600.120-24']).isValid()).toBe(false);
    });

    it('blacklist() overrides valid result', () => {
        expect(new CPFValidation('864.600.120-24').denyList(['864.600.120-24']).isValid()).toBe(false);
    });

    it('validateOrFail() returns true when valid', () => {
        expect(new CPFValidation('864.600.120-24').validateOrFail()).toBeUndefined();
    });

    it('validateOrFail() throws ValidationException when invalid', () => {
        expect(() => new CPFValidation('323.543.123-43').validateOrFail()).toThrow(ValidationException);
        expect(() => new CPFValidation('323.543.123-43').validateOrFail()).toThrow('cpf: bad_check_digit');
    });

    it('validateOrFail() respects allowList and denyList', () => {
        expect(new CPFValidation('323.543.123-43').allowList(['323.543.123-43']).validateOrFail()).toBeUndefined();
        expect(() => new CPFValidation('864.600.120-24').denyList(['864.600.120-24']).validateOrFail()).toThrow(ValidationException);
    });

    it('deprecated whitelist()/blacklist() still work as aliases', () => {
        expect(new CPFValidation('323.543.123-43').whitelist(['323.543.123-43']).isValid()).toBe(true);
        expect(new CPFValidation('864.600.120-24').blacklist(['864.600.120-24']).isValid()).toBe(false);
    });

    it('validate() returns a rich result (valid: normalized + fiscal-region meta)', () => {
        const r = new CPFValidation('864.600.120-24').validate();
        expect(r.valid).toBe(true);
        expect(r.reason).toBeNull();
        expect(r.normalized).toBe('86460012024');
        expect(r.meta?.uf).toBe('RS'); // 9th digit (index 8) is 0 -> fiscal region RS
    });

    it('meta has the full fixed shape (all fields present, non-applicable ones null) for PHP parity', () => {
        const meta = new CPFValidation('864.600.120-24').validate().meta;
        expect(meta).toEqual({
            uf: 'RS',
            type: null,
            brand: null,
            keyType: null,
            isMatriz: null,
            isAlphanumeric: null,
            pattern: null,
        });
    });

    it('allow-listed values are valid but carry no metadata (meta is null)', () => {
        // The value was force-accepted, not proven a real document, so no meta is extracted.
        const r = new CPFValidation('123').allowList(['123']).validate();
        expect(r.valid).toBe(true);
        expect(r.meta).toBeNull();
    });

    it('validate() surfaces the reason on failure', () => {
        expect(new CPFValidation('9999999999').validate().reason).toBe('wrong_length');
        expect(new CPFValidation('00000000000').validate().reason).toBe('known_invalid');
        expect(new CPFValidation('323.543.123-43').validate().reason).toBe('bad_check_digit');
        expect(new CPFValidation('864.600.120-24').denyList(['86460012024']).validate().reason).toBe('denied');
    });

    it('validateOrFail() throws with structured context', () => {
        try {
            new CPFValidation('323.543.123-43').validateOrFail();
            expect.unreachable();
        } catch (e) {
            expect(e).toBeInstanceOf(ValidationException);
            const ve = e as ValidationException;
            expect(ve.document).toBe('cpf');
            expect(ve.reason).toBe('bad_check_digit');
            expect(ve.normalized).toBe('32354312343');
            expect(ve.message).toBe('cpf: bad_check_digit');
        }
    });
});
