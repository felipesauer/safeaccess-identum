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
}

const vectorsPath = fileURLToPath(new URL('../../../parity-vectors.json', import.meta.url));
const vectors = JSON.parse(readFileSync(vectorsPath, 'utf-8')) as Record<string, ParityCase[]>;

const verb = (valid: boolean): string => (valid ? 'accepts' : 'rejects');

describe('Parity — same input, same output in PHP and TypeScript', () => {
    describe('CPF', () => {
        for (const c of vectors.cpf) {
            it(`${verb(c.valid)} ${c.input}`, () =>
                expect(Identum.cpf(c.input).validate()).toBe(c.valid));
        }
    });

    describe('CNPJ', () => {
        for (const c of vectors.cnpj) {
            it(`${verb(c.valid)} ${c.input}`, () =>
                expect(Identum.cnpj(c.input).validate()).toBe(c.valid));
        }
    });

    describe('CNH', () => {
        for (const c of vectors.cnh) {
            it(`${verb(c.valid)} ${c.input}`, () =>
                expect(Identum.cnh(c.input).validate()).toBe(c.valid));
        }
    });

    describe('CEP', () => {
        for (const c of vectors.cep) {
            it(`${verb(c.valid)} ${c.input}`, () =>
                expect(Identum.cep(c.input).validate()).toBe(c.valid));
        }
    });

    describe('CNS', () => {
        for (const c of vectors.cns) {
            it(`${verb(c.valid)} ${c.input}`, () =>
                expect(Identum.cns(c.input).validate()).toBe(c.valid));
        }
    });

    describe('PIS', () => {
        for (const c of vectors.pis) {
            it(`${verb(c.valid)} ${c.input}`, () =>
                expect(Identum.pis(c.input).validate()).toBe(c.valid));
        }
    });

    describe('IE', () => {
        for (const c of vectors.ie) {
            const state = StateEnum[c.state as keyof typeof StateEnum];
            it(`${verb(c.valid)} ${c.input} (${c.state})`, () =>
                expect(Identum.ie(c.input, state).validate()).toBe(c.valid));
        }
    });

    describe('RENAVAM', () => {
        for (const c of vectors.renavam) {
            it(`${verb(c.valid)} ${c.input}`, () =>
                expect(Identum.renavam(c.input).validate()).toBe(c.valid));
        }
    });

    describe('Plate (Mercosul)', () => {
        for (const c of vectors.placa) {
            it(`${verb(c.valid)} ${c.input}`, () =>
                expect(Identum.placa(c.input).validate()).toBe(c.valid));
        }
    });

    describe('Voter Title', () => {
        for (const c of vectors.tituloEleitor) {
            it(`${verb(c.valid)} ${c.input}`, () =>
                expect(Identum.tituloEleitor(c.input).validate()).toBe(c.valid));
        }
    });
});
