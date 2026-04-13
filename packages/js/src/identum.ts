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
import type { StateCode } from './assets/ie/state-enum.js';

/**
 * Main entry point for document validation.
 *
 * Provides static aliases for all supported document validators,
 * resolved dynamically via the alias registry.
 *
 * @see {@link ValidatableDocument} Contract implemented by all validators.
 */
export class Identum {
    static cpf(document: string): CPFValidation {
        return new CPFValidation(document);
    }

    static cnpj(document: string): CNPJValidation {
        return new CNPJValidation(document);
    }

    static cnh(document: string): CNHValidation {
        return new CNHValidation(document);
    }

    static cep(document: string): CEPValidation {
        return new CEPValidation(document);
    }

    static cns(document: string): CNSValidation {
        return new CNSValidation(document);
    }

    static pis(document: string): PISValidation {
        return new PISValidation(document);
    }

    static ie(document: string, state: StateCode | number): IEValidation {
        return new IEValidation(document, state);
    }

    static renavam(document: string): RenavamValidation {
        return new RenavamValidation(document);
    }

    static placa(document: string): PlateMercosulValidation {
        return new PlateMercosulValidation(document);
    }

    static tituloEleitor(document: string): VoterTitleValidation {
        return new VoterTitleValidation(document);
    }
}
