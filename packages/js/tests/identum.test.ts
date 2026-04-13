import { describe, it, expect } from 'vitest';
import { Identum } from '../src/identum.js';
import { ValidationException } from '../src/exceptions/validation-exception.js';
import { CPFValidation } from '../src/assets/cpf/cpf-validation.js';
import { CNPJValidation } from '../src/assets/cnpj/cnpj-validation.js';
import { CNHValidation } from '../src/assets/cnh/cnh-validation.js';
import { PISValidation } from '../src/assets/pis/pis-validation.js';
import { CNSValidation } from '../src/assets/cns/cns-validation.js';
import { RenavamValidation } from '../src/assets/renavam/renavam-validation.js';
import { CEPValidation } from '../src/assets/cep/cep-validation.js';
import { IEValidation } from '../src/assets/ie/ie-validation.js';
import { PlateMercosulValidation } from '../src/assets/plate/plate-mercosul-validation.js';
import { VoterTitleValidation } from '../src/assets/voter/voter-title-validation.js';
import { StateEnum } from '../src/assets/ie/state-enum.js';

describe(Identum.name, () => {
    it('resolves CPF via static alias', () => {
        const v = Identum.cpf('864.600.120-24');
        expect(v).toBeInstanceOf(CPFValidation);
        expect(v.validate()).toBe(true);
    });

    it('resolves CNPJ via static alias (numeric)', () => {
        const v = Identum.cnpj('84.773.274/0001-03');
        expect(v).toBeInstanceOf(CNPJValidation);
        expect(v.validate()).toBe(true);
    });

    it('resolves CNPJ via static alias (alphanumeric)', () => {
        const v = Identum.cnpj('A0000000000032');
        expect(v).toBeInstanceOf(CNPJValidation);
        expect(v.validate()).toBe(true);
    });

    it('resolves CNH via static alias', () => {
        const v = Identum.cnh('22522791508');
        expect(v).toBeInstanceOf(CNHValidation);
        expect(v.validate()).toBe(true);
    });

    it('resolves PIS via static alias', () => {
        const v = Identum.pis('32995061589');
        expect(v).toBeInstanceOf(PISValidation);
        expect(v.validate()).toBe(true);
    });

    it('resolves CNS via static alias', () => {
        const v = Identum.cns('100000000000007');
        expect(v).toBeInstanceOf(CNSValidation);
        expect(v.validate()).toBe(true);
    });

    it('resolves RENAVAM via static alias', () => {
        const v = Identum.renavam('60390908553');
        expect(v).toBeInstanceOf(RenavamValidation);
        expect(v.validate()).toBe(true);
    });

    it('resolves CEP via static alias', () => {
        const v = Identum.cep('01001-000');
        expect(v).toBeInstanceOf(CEPValidation);
        expect(v.validate()).toBe(true);
    });

    it('resolves IE via static alias with state parameter', () => {
        const v = Identum.ie('209644419', StateEnum.GO);
        expect(v).toBeInstanceOf(IEValidation);
        expect(v.validate()).toBe(true);
    });

    it('resolves placa via static alias', () => {
        const v = Identum.placa('BRA1A23');
        expect(v).toBeInstanceOf(PlateMercosulValidation);
        expect(v.validate()).toBe(true);
    });

    it('resolves tituloEleitor via static alias', () => {
        const v = Identum.tituloEleitor('123456781295');
        expect(v).toBeInstanceOf(VoterTitleValidation);
        expect(v.validate()).toBe(true);
    });

    it('returned validator supports whitelist()', () => {
        expect(Identum.cpf('323.543.123-43').whitelist(['323.543.123-43']).validate()).toBe(true);
    });

    it('returned validator supports blacklist()', () => {
        expect(Identum.cpf('864.600.120-24').blacklist(['864.600.120-24']).validate()).toBe(false);
    });

    it('returned validator supports validateOrFail()', () => {
        expect(Identum.cpf('864.600.120-24').validateOrFail()).toBe(true);
        expect(() => Identum.cpf('323.543.123-43').validateOrFail()).toThrow(ValidationException);
    });
});
