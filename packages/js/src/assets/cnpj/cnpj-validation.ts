import { AbstractValidatableDocument } from '../../contracts/abstract-validatable-document.js';

/**
 * Validates Brazilian CNPJ (Cadastro Nacional da Pessoa Jurídica) numbers.
 *
 * Supports both numeric and alphanumeric CNPJ formats.
 */
export class CNPJValidation extends AbstractValidatableDocument {
    protected doValidate(): boolean {
        const raw = this._raw.toUpperCase();
        const txt = raw.replace(/[\s.\-/]/g, '');

        if (txt.length !== 14) {
            return false;
        }

        if (!/^\d{2}$/.test(txt.slice(12))) {
            return false;
        }

        if (/^\d{14}$/.test(txt) && /^(\d)\1{13}$/.test(txt)) {
            return false;
        }

        const body12 = txt.slice(0, 12);
        const dvIn1 = Number(txt[12]);
        const dvIn2 = Number(txt[13]);

        const val = (ch: string): number => {
            const o = ch.charCodeAt(0);
            if (o >= 48 && o <= 57) return o - 48;
            if (o >= 65 && o <= 90) return o - 48;
            return -1;
        };

        const w1 = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        const w2 = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

        let sum = 0;
        for (let i = 0; i < 12; i++) {
            const v = val(body12[i]);
            if (v < 0) return false;
            sum += v * w1[i];
        }
        const rest1 = sum % 11;
        const dv1 = rest1 < 2 ? 0 : 11 - rest1;

        sum = 0;
        for (let i = 0; i < 12; i++) {
            sum += val(body12[i]) * w2[i];
        }
        sum += dv1 * w2[12];
        const rest2 = sum % 11;
        const dv2 = rest2 < 2 ? 0 : 11 - rest2;

        return dvIn1 === dv1 && dvIn2 === dv2;
    }
}
