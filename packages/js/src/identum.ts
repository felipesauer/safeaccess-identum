import { CPFValidation } from './assets/cpf/cpf-validation.js';
import { CNPJValidation } from './assets/cnpj/cnpj-validation.js';
import { CNHValidation } from './assets/cnh/cnh-validation.js';
import { CEPValidation } from './assets/cep/cep-validation.js';
import { CNSValidation } from './assets/cns/cns-validation.js';
import { PISValidation } from './assets/pis/pis-validation.js';
import { IEValidation } from './assets/ie/ie-validation.js';
import { RenavamValidation } from './assets/renavam/renavam-validation.js';
import { PlateMercosulValidation } from './assets/plate/plate-mercosul-validation.js';
import { VoterTitleValidation } from './assets/voter/voter-title-validation.js';
import { CartaoValidation } from './assets/cartao/cartao-validation.js';
import { CertidaoValidation } from './assets/certidao/certidao-validation.js';
import { PixValidation } from './assets/pix/pix-validation.js';
import type { StateCode } from './assets/ie/state-enum.js';

/**
 * Main entry point for document validation.
 *
 * Exposes one concrete static factory per supported document type, plus a
 * matching generator (generateXxx) for the types that support generation.
 *
 * @see {@link ValidatableDocument} Contract implemented by all validators.
 */
export class Identum {
    static cpf(document: string): CPFValidation {
        return new CPFValidation(document);
    }

    /** Generates a valid CPF (unmasked by default). */
    static generateCpf(formatted = false): string {
        return CPFValidation.generate(formatted);
    }

    static cnpj(document: string): CNPJValidation {
        return new CNPJValidation(document);
    }

    /** Generates a valid (numeric) CNPJ (unmasked by default). */
    static generateCnpj(formatted = false): string {
        return CNPJValidation.generate(formatted);
    }

    static cnh(document: string): CNHValidation {
        return new CNHValidation(document);
    }

    /** Generates a valid CNH. */
    static generateCnh(): string {
        return CNHValidation.generate();
    }

    static cartao(document: string): CartaoValidation {
        return new CartaoValidation(document);
    }

    static cep(document: string): CEPValidation {
        return new CEPValidation(document);
    }

    /** Generates a valid CEP (unmasked by default). */
    static generateCep(formatted = false): string {
        return CEPValidation.generate(formatted);
    }

    static certidao(document: string): CertidaoValidation {
        return new CertidaoValidation(document);
    }

    static cns(document: string): CNSValidation {
        return new CNSValidation(document);
    }

    /** Generates a valid CNS (provisional type; unmasked by default). */
    static generateCns(formatted = false): string {
        return CNSValidation.generate(formatted);
    }

    static pis(document: string): PISValidation {
        return new PISValidation(document);
    }

    /** Generates a valid PIS/PASEP (unmasked by default). */
    static generatePis(formatted = false): string {
        return PISValidation.generate(formatted);
    }

    static pix(key: string): PixValidation {
        return new PixValidation(key);
    }

    static ie(document: string, state: StateCode | number): IEValidation {
        return new IEValidation(document, state);
    }

    /** Generates a valid IE for the given state (unmasked). */
    static generateIe(state: StateCode | number): string {
        return IEValidation.generate(state);
    }

    static renavam(document: string): RenavamValidation {
        return new RenavamValidation(document);
    }

    /** Generates a valid RENAVAM. */
    static generateRenavam(): string {
        return RenavamValidation.generate();
    }

    static placa(document: string): PlateMercosulValidation {
        return new PlateMercosulValidation(document);
    }

    /** Generates a valid Mercosul plate. */
    static generatePlaca(): string {
        return PlateMercosulValidation.generate();
    }

    static tituloEleitor(document: string): VoterTitleValidation {
        return new VoterTitleValidation(document);
    }

    /** Generates a valid Voter Title. */
    static generateTituloEleitor(): string {
        return VoterTitleValidation.generate();
    }
}
