import { AbstractValidatableDocument } from '../../contracts/abstract-validatable-document.js';

/**
 * Validates Brazilian CNS (Cartão Nacional de Saúde) numbers.
 */
export class CNSValidation extends AbstractValidatableDocument {
    protected doValidate(): boolean {
        const digits = this._raw.replace(/\D+/g, '');

        if (digits.length !== 15) {
            return false;
        }

        const first = Number(digits[0]);

        if (first === 1 || first === 2) {
            const pis = digits.slice(0, 11);

            let sum = 0;
            for (let i = 0, w = 15; i < 11; i++, w--) {
                sum += Number(pis[i]) * w;
            }

            const rest = sum % 11;
            let dv = 11 - rest;

            let resultado: string;
            if (dv === 11) {
                dv = 0;
                resultado = pis + '000' + String(dv);
            } else if (dv === 10) {
                sum += 2;
                dv = 11 - (sum % 11);
                resultado = pis + '001' + String(dv);
            } else {
                resultado = pis + '000' + String(dv);
            }

            return digits === resultado;
        }

        if (first === 7 || first === 8 || first === 9) {
            let sum = 0;
            for (let i = 0, w = 15; i < 15; i++, w--) {
                sum += Number(digits[i]) * w;
            }
            return sum % 11 === 0;
        }

        return false;
    }
}
