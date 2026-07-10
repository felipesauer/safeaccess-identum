<p align="center">
  <img src="https://raw.githubusercontent.com/felipesauer/safeaccess-identum/main/.github/assets/logo.svg" width="80" alt="safeaccess-identum logo">
</p>

<h1 align="center">Safe Access Identum — TypeScript</h1>

TypeScript/JavaScript library for validating Brazilian documents — CPF, CNPJ, CNH, CEP, CNS, PIS, IE (all 27 states), RENAVAM, Mercosul Plate, Voter Title, Payment Card, PIX key, and Certificate. ESM, input sanitization by default, zero production dependencies.

<p align="center">
  <a href="https://www.npmjs.com/package/@safeaccess/identum"><img src="https://img.shields.io/npm/v/@safeaccess/identum?label=npm" alt="npm"></a>
  <a href="../../LICENSE"><img src="https://img.shields.io/badge/License-MIT-blue.svg" alt="License: MIT"></a>
  <img src="https://img.shields.io/badge/Node.js-22%2B-339933?logo=nodedotjs&amp;logoColor=white" alt="Node.js 22+">
  <img src="https://img.shields.io/badge/ESM-native-F7DF1E?logo=javascript&amp;logoColor=black" alt="ESM native">
  <img src="https://img.shields.io/badge/Tested%20with-Vitest-6E9F18" alt="Tested with Vitest">
  <img src="https://img.shields.io/endpoint?style=flat&amp;url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Ffelipesauer%2Fsafeaccess-identum%2Fmain" alt="Stryker MSI">
</p>

---

> **Version 2.0.** `validate()` now returns a rich result object instead of a boolean, with new capabilities (`format`, `generate`, metadata), new document types, and tree-shakeable subpath imports. Upgrading from 1.x? See [Migrating from 1.x](https://github.com/felipesauer/safeaccess-identum#migrating-from-1x).

## Features

- **13 document types** — CPF, CNPJ (alphanumeric), CNH, CEP, CNS, PIS, IE (all 27 states), RENAVAM, Mercosul Plate, Voter Title, plus **Payment Card (Luhn), PIX key, and civil-registry Certificate**
- **Rich result** — `validate()` returns `{ valid, reason, normalized, meta }`; `isValid()` is the boolean shortcut
- **Machine-readable reasons** — every failure carries a stable `ReasonCode` (`invalid_format`, `wrong_length`, `bad_check_digit`, `unknown_uf`, `known_invalid`, `denied`) — ideal as i18n keys
- **Metadata extraction** — offline, from the number: CPF/IE `uf`, CNS `type`, CNPJ `isMatriz`/`isAlphanumeric`, Card `brand`, PIX `keyType`, Certificate `type`
- **`format()` / `strip()`** — apply or remove the canonical mask
- **`generate()`** — valid documents for tests (`Identum.generateCpf()`, …)
- **Tree-shakeable** — `sideEffects: false` + per-document subpath exports (`@safeaccess/identum/cpf`)
- **IE all 27 states** — every state algorithm implemented and tested with edge cases
- **Input sanitization by default** — `'529.982.247-25'` and `'52998224725'` both just work
- **Allow list / deny list** — force-accept or force-reject specific values (allow list wins)
- **100% line + branch coverage** — tested with Vitest · Stryker mutation testing (≥ 85% MSI)
- **Native ESM** — no CommonJS fallback
- **Zero production dependencies** — pure TypeScript

## The problem

Validating Brazilian documents in JavaScript/TypeScript means maintaining arithmetic loops for CPF, two-pass Mod-11 for CNPJ, and 27 algorithms for IE — each in a different file, none with parity tests.

**Without this library:**

```typescript
function validateCpf(cpf: string): boolean {
    cpf = cpf.replace(/\D/g, '');
    if (cpf.length !== 11 || /^(\d)\1+$/.test(cpf)) return false;
    // loops, running sums, modulus, digit comparison...
}
```

**With this library:**

```typescript
Identum.cpf('529.982.247-25').isValid();                  // true
Identum.ie('343173196450', StateEnum.SP).isValid();       // true — all 27 states
```

## Installation

```bash
npm install @safeaccess/identum
```

**Requirements:** Node.js 22+

## Quick start

```typescript
import { Identum, StateEnum, ValidationException } from '@safeaccess/identum';

// Boolean shortcut — formatting stripped automatically
Identum.cpf('529.982.247-25').isValid();                    // true
Identum.cnpj('84.773.274/0001-03').isValid();               // true
Identum.cnpj('A0000000000032').isValid();                   // true — alphanumeric CNPJ
Identum.cnh('22522791508').isValid();                       // true
Identum.cep('78000-000').isValid();                         // true
Identum.cns('100000000060018').isValid();                   // true
Identum.pis('329.9506.158-9').isValid();                    // true
Identum.ie('343173196450', StateEnum.SP).isValid();         // true — all 27 states
Identum.renavam('60390908553').isValid();                   // true
Identum.placa('ABC1D23').isValid();                         // true — Mercosul format
Identum.tituloEleitor('123456781295').isValid();            // true
Identum.cartao('4111111111111111').isValid();               // true — Luhn
Identum.pix('pix@bcb.gov.br').isValid();                    // true — PIX key
Identum.certidao('00188301551987100018050000056665').isValid(); // true — certificate

// Rich result — why it failed, the normalized value, and extracted metadata
const result = Identum.cpf('529.982.247-25').validate();
result.valid;       // true
result.reason;      // null (a ReasonCode when invalid)
result.normalized;  // '52998224725'
result.meta?.uf;    // 'SP' — fiscal region

// Validate or throw — the exception carries structured context
try {
    Identum.cpf('000.000.000-00').validateOrFail();
} catch (e) {
    if (e instanceof ValidationException) {
        e.document;   // 'cpf'
        e.reason;     // 'known_invalid'
        e.normalized; // '00000000000'
    }
}

// Allow list / deny list (format-agnostic)
Identum.cpf('529.982.247-25').denyList(['52998224725']).isValid();   // false
Identum.cpf('000.000.000-00').allowList(['000.000.000-00']).isValid(); // true

// Format / strip / generate
Identum.cpf('52998224725').format();     // '529.982.247-25'
Identum.cpf('529.982.247-25').strip();   // '52998224725'
Identum.generateCpf();                    // e.g. '76502099010'
Identum.generateCnpj(true);               // e.g. '12.345.678/0001-95'
```

## Direct instantiation

```typescript
import { CPFValidation } from '@safeaccess/identum';

const validator = new CPFValidation('529.982.247-25');
validator.isValid(); // true
```

### Tree-shaking

Import a single validator through its subpath so bundlers drop everything else:

```typescript
import { CPFValidation } from '@safeaccess/identum/cpf'; // only CPF ends up in the bundle
```

Every document has a subpath (`/cpf`, `/cnpj`, `/ie`, `/cartao`, `/pix`, `/certidao`, …). The package is marked `sideEffects: false`. The root barrel (`@safeaccess/identum`) still re-exports everything.

## API

All validator classes share the same fluent interface after construction:

| Method | Return | Description |
| --- | --- | --- |
| `validate()` | `ValidationResult` | Rich result: `{ valid, reason, normalized, meta }` |
| `isValid()` | `boolean` | Boolean shortcut for `validate().valid` |
| `validateOrFail()` | `void` | Throws `ValidationException` (with `document`, `reason`, `normalized`) when invalid |
| `format()` | `string` | Canonical mask applied, best-effort |
| `strip()` | `string` | Canonical value with mask characters removed |
| `denyList(string[])` | `this` | Force-reject the specified values regardless of checksum |
| `allowList(string[])` | `this` | Force-accept the specified values regardless of checksum |
| `raw()` | `string` | The input exactly as provided |

> `blacklist()` / `whitelist()` still work as deprecated aliases of `denyList()` / `allowList()` and will be removed in 3.0.

**Reason codes** (stable, `snake_case`), in the order they are checked: `invalid_format` → `wrong_length` → `bad_check_digit` → `unknown_uf` → `known_invalid` → `denied`.

**Generators** — one per type on the facade: `generateCpf()`, `generateCnpj()`, `generateCnh()`, `generateCep()`, `generateCns()`, `generatePis()`, `generateIe(state)`, `generateRenavam()`, `generatePlaca()`, `generateTituloEleitor()`. Unmasked by default; pass `true` where a mask exists.

## Supported documents

| Document       | Alias           | Class                     |
| -------------- | --------------- | ------------------------- |
| CPF            | `cpf`           | `CPFValidation`           |
| CNPJ           | `cnpj`          | `CNPJValidation`          |
| CNH            | `cnh`           | `CNHValidation`           |
| CEP            | `cep`           | `CEPValidation`           |
| CNS            | `cns`           | `CNSValidation`           |
| PIS/PASEP      | `pis`           | `PISValidation`           |
| IE             | `ie`            | `IEValidation`            |
| RENAVAM        | `renavam`       | `RenavamValidation`       |
| Mercosul Plate | `placa`         | `PlateMercosulValidation` |
| Voter Title    | `tituloEleitor` | `VoterTitleValidation`    |
| Payment Card   | `cartao`        | `CartaoValidation`        |
| PIX key        | `pix`           | `PixValidation`           |
| Certificate    | `certidao`      | `CertidaoValidation`      |

### IE — all 27 states

```typescript
import { Identum, StateEnum } from '@safeaccess/identum';

Identum.ie('153189458', StateEnum.BA).isValid();     // Bahia — Mod-10/11 dual
Identum.ie('7908930932562', StateEnum.MG).isValid(); // Minas Gerais
Identum.ie('P199163724045', StateEnum.SP).isValid(); // São Paulo rural (P prefix)
```

### CNPJ — alphanumeric

```typescript
Identum.cnpj('A0000000000032').isValid(); // true — alphanumeric CNPJ
```

### Payment Card, PIX and Certificate

```typescript
// Payment card — Luhn integrity only; meta.brand is best-effort BIN detection
Identum.cartao('4111111111111111').validate().meta?.brand; // 'visa'

// PIX — any of the five DICT key types; meta.keyType tells which
Identum.pix('+5510998765432').validate().meta?.keyType;    // 'phone'

// Civil-registry certificate — 32-digit matrícula (Mod-11 ×10)
Identum.certidao('00188301551987100018050000056665').validate().meta?.type; // 'birth'
```

> Card validation is Luhn integrity plus best-effort brand — it does **not** prove a card exists (that needs an online lookup). PIX validates key **format** (and CPF/CNPJ checksums), not DICT registration. Both are offline by design.

## Contributing

See [CONTRIBUTING.md](../../CONTRIBUTING.md) for development setup, commit conventions, and pull request guidelines.

## License

[MIT](../../LICENSE) © Felipe Sauer
