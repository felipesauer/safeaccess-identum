// Main facade
export { Identum } from './identum.js';

// Validators
export { CPFValidation } from './assets/cpf/cpf-validation.js';
export { CNPJValidation } from './assets/cnpj/cnpj-validation.js';
export { CNHValidation } from './assets/cnh/cnh-validation.js';
export { CEPValidation } from './assets/cep/cep-validation.js';
export { CNSValidation } from './assets/cns/cns-validation.js';
export { PISValidation } from './assets/pis/pis-validation.js';
export { IEValidation } from './assets/ie/ie-validation.js';
export { RenavamValidation } from './assets/renavam/renavam-validation.js';
export { PlateMercosulValidation } from './assets/plate/plate-mercosul-validation.js';
export { VoterTitleValidation } from './assets/voter/voter-title-validation.js';
export { CartaoValidation } from './assets/cartao/cartao-validation.js';
export { CertidaoValidation } from './assets/certidao/certidao-validation.js';
export { PixValidation } from './assets/pix/pix-validation.js';

// IE
export { StateEnum } from './assets/ie/state-enum.js';
export type { StateCode } from './assets/ie/state-enum.js';

// Contracts
export { AbstractValidatableDocument } from './contracts/abstract-validatable-document.js';
export type { ValidatableDocument } from './contracts/abstract-validatable-document.js';
export { ReasonCode } from './contracts/reason-code.js';
export type { ValidationResult, DocumentMeta } from './contracts/validation-result.js';

// Exceptions
export { ValidationException } from './exceptions/validation-exception.js';
export { InvalidStateRuleException } from './exceptions/invalid-state-rule-exception.js';
