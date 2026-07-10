import { describe, it, expect } from 'vitest';
import { readFileSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import { Identum, StateEnum } from '../src/index.js';

/**
 * Cross-language parity tests.
 *
 * The canonical ground truth lives in the repository-root `parity-vectors.json`,
 * loaded by BOTH packages — this file and packages/php/tests/Unit/ParityTest.php.
 * Add or change cases ONLY in that JSON so PHP and TypeScript can never drift.
 */

interface ParityCase {
    input: string;
    valid: boolean;
    state?: string;
    reason?: string | null;
}

const vectorsPath = fileURLToPath(new URL('../../../parity-vectors.json', import.meta.url));
const vectors = JSON.parse(readFileSync(vectorsPath, 'utf-8')) as Record<string, ParityCase[]>;

const verb = (valid: boolean): string => (valid ? 'accepts' : 'rejects');

/** Asserts validity and, when the vector pins one, the machine-readable reason. */
function assertCase(actual: { valid: boolean; reason: string | null }, c: ParityCase): void {
    expect(actual.valid).toBe(c.valid);
    if ('reason' in c) {
        expect(actual.reason).toBe(c.reason);
    }
}

describe('Parity — same input, same output in PHP and TypeScript', () => {
    const simple: Record<string, (input: string) => { valid: boolean; reason: string | null }> = {
        CPF: (i) => Identum.cpf(i).validate(),
        CNPJ: (i) => Identum.cnpj(i).validate(),
        CNH: (i) => Identum.cnh(i).validate(),
        CEP: (i) => Identum.cep(i).validate(),
        CNS: (i) => Identum.cns(i).validate(),
        PIS: (i) => Identum.pis(i).validate(),
        RENAVAM: (i) => Identum.renavam(i).validate(),
        'Plate (Mercosul)': (i) => Identum.placa(i).validate(),
        'Voter Title': (i) => Identum.tituloEleitor(i).validate(),
        Certidão: (i) => Identum.certidao(i).validate(),
        Cartão: (i) => Identum.cartao(i).validate(),
        PIX: (i) => Identum.pix(i).validate(),
    };

    const aliasByLabel: Record<string, string> = {
        CPF: 'cpf',
        CNPJ: 'cnpj',
        CNH: 'cnh',
        CEP: 'cep',
        CNS: 'cns',
        PIS: 'pis',
        RENAVAM: 'renavam',
        'Plate (Mercosul)': 'placa',
        'Voter Title': 'tituloEleitor',
        Certidão: 'certidao',
        Cartão: 'cartao',
        PIX: 'pix',
    };

    for (const [label, run] of Object.entries(simple)) {
        describe(label, () => {
            for (const c of vectors[aliasByLabel[label]]) {
                it(`${verb(c.valid)} ${c.input}`, () => assertCase(run(c.input), c));
            }
        });
    }

    describe('IE', () => {
        for (const c of vectors.ie) {
            const state = StateEnum[c.state as keyof typeof StateEnum];
            it(`${verb(c.valid)} ${c.input} (${c.state})`, () =>
                assertCase(Identum.ie(c.input, state).validate(), c));
        }
    });
});
