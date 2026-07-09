import { describe, it, expect } from 'vitest';
import { Identum } from '../src/index.js';

describe('format() applies the canonical mask; strip() removes it', () => {
    it('CPF', () => {
        expect(Identum.cpf('52998224725').format()).toBe('529.982.247-25');
        expect(Identum.cpf('529.982.247-25').strip()).toBe('52998224725');
    });

    it('CNPJ (numeric and alphanumeric)', () => {
        expect(Identum.cnpj('84773274000103').format()).toBe('84.773.274/0001-03');
        expect(Identum.cnpj('A0000000000032').format()).toBe('A0.000.000/0000-32');
        expect(Identum.cnpj('84.773.274/0001-03').strip()).toBe('84773274000103');
    });

    it('CEP', () => {
        expect(Identum.cep('78000000').format()).toBe('78000-000');
        expect(Identum.cep('78000-000').strip()).toBe('78000000');
    });

    it('PIS', () => {
        expect(Identum.pis('32995061589').format()).toBe('329.95061.58-9');
    });

    it('CNS', () => {
        expect(Identum.cns('100000000060018').format()).toBe('100 0000 0006 0018');
    });

    it('is best-effort: returns the stripped value when it does not fit the mask', () => {
        expect(Identum.cpf('529').format()).toBe('529');
        expect(Identum.cep('780').format()).toBe('780');
    });

    it('documents without a canonical mask return the stripped value unchanged', () => {
        expect(Identum.placa('ABC1D23').format()).toBe('ABC1D23');
        expect(Identum.cnh('22522791508').format()).toBe('22522791508');
        expect(Identum.renavam('60390908553').format()).toBe('60390908553');
        expect(Identum.tituloEleitor('123456781295').format()).toBe('123456781295');
    });
});
