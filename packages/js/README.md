<p align="center">
  <img src="https://raw.githubusercontent.com/felipesauer/safeaccess-identum/main/.github/assets/logo.svg" width="80" alt="safeaccess-identum logo">
</p>

<h1 align="center">Safe Access Identum — TypeScript</h1>

<p align="center">
  <a href="https://www.npmjs.com/package/@safeaccess/identum"><img src="https://img.shields.io/npm/v/@safeaccess/identum?label=npm" alt="npm"></a>
  <a href="../../LICENSE"><img src="https://img.shields.io/badge/License-MIT-blue.svg" alt="License: MIT"></a>
  <img src="https://img.shields.io/badge/Node.js-22%2B-339933?logo=nodedotjs&amp;logoColor=white" alt="Node.js 22+">
  <img src="https://img.shields.io/badge/ESM-native-F7DF1E?logo=javascript&amp;logoColor=black" alt="ESM native">
  <img src="https://img.shields.io/badge/Tested%20with-Vitest-6E9F18" alt="Tested with Vitest">
  <img src="https://img.shields.io/endpoint?style=flat&amp;url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Ffelipesauer%2Fsafeaccess-identum%2Fmain" alt="Stryker MSI">
</p>

---

TypeScript/JavaScript library for validating Brazilian documents — CPF, CNPJ, CNH, CEP, CNS, PIS, IE (all 27 states), RENAVAM, Mercosul Plate, and Voter Title. ESM, input sanitization by default, zero production dependencies.

## Features

- **10 document types** — CPF, CNPJ (alphanumeric), CNH, CEP, CNS, PIS, IE (all 27 states), RENAVAM, Mercosul Plate, Voter Title
- **IE all 27 states** — every state algorithm implemented and tested with edge cases
- **Input sanitization by default** — `'529.982.247-25'` and `'52998224725'` both just work
- **`validateOrFail()`** — throws `ValidationException` instead of returning `false`
- **Blacklist / whitelist** — force-accept or force-reject specific values
- **100% line + branch coverage** — tested with Vitest · Stryker mutation testing (≥ 85% MSI)
- **Native ESM** — no CommonJS fallback, tree-shakeable
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
Identum.cpf('529.982.247-25').validate();                  // true
Identum.ie('343173196450', StateEnum.SP).validate();       // true — all 27 states
```

## Installation

```bash
npm install @safeaccess/identum
```

**Requirements:** Node.js 22+

## Quick start

```typescript
import { Identum, StateEnum, ValidationException } from '@safeaccess/identum';

// All document types — formatting stripped automatically
Identum.cpf('529.982.247-25').validate();                    // true
Identum.cnpj('84.773.274/0001-03').validate();               // true
Identum.cnpj('A0000000000032').validate();                   // true — alphanumeric CNPJ
Identum.cnh('22522791508').validate();                       // true
Identum.cep('78000-000').validate();                         // true
Identum.cns('100000000060018').validate();                   // true
Identum.pis('329.9506.158-9').validate();                    // true
Identum.ie('343173196450', StateEnum.SP).validate();         // true — all 27 states
Identum.renavam('60390908553').validate();                   // true
Identum.placa('ABC1D23').validate();                         // true — Mercosul format
Identum.tituloEleitor('123456781295').validate();            // true

// Validate or throw
try {
    Identum.cpf('000.000.000-00').validateOrFail();
} catch (e) {
    if (e instanceof ValidationException) {
        // handle invalid document
    }
}

// Blacklist / whitelist
Identum.cpf('529.982.247-25').blacklist(['529.982.247-25']).validate(); // false
Identum.cpf('000.000.000-00').whitelist(['000.000.000-00']).validate(); // true
```

## Direct instantiation

```typescript
import { CPFValidation } from '@safeaccess/identum';

const validator = new CPFValidation('529.982.247-25');
validator.validate(); // true
```

## API

All validator classes share the same fluent interface after construction:

```typescript
const v = Identum.cpf('52998224725'); // or new CPFValidation('52998224725')

v.validate();                                  // boolean
v.validateOrFail();                            // boolean — throws ValidationException if invalid
v.blacklist(['52998224725']).validate();        // boolean — force-reject these values
v.whitelist(['00000000000']).validate();        // boolean — force-accept these values
```

| Method | Return | Description |
| --- | --- | --- |
| `validate()` | `boolean` | Returns `true` if valid, `false` otherwise |
| `validateOrFail()` | `boolean` | Returns `true` if valid, throws `ValidationException` otherwise |
| `blacklist(string[])` | `this` | Force-reject the specified values regardless of checksum |
| `whitelist(string[])` | `this` | Force-accept the specified values regardless of checksum |

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

### IE — all 27 states

```typescript
import { Identum, StateEnum } from '@safeaccess/identum';

Identum.ie('153189458', StateEnum.BA).validate();     // Bahia — Mod-10/11 dual
Identum.ie('7908930932562', StateEnum.MG).validate(); // Minas Gerais
Identum.ie('P199163724045', StateEnum.SP).validate(); // São Paulo rural (P prefix)
```

### CNPJ — alphanumeric

```typescript
Identum.cnpj('A0000000000032').validate(); // true — alphanumeric CNPJ
```

## Contributing

See [CONTRIBUTING.md](../../CONTRIBUTING.md) for development setup, commit conventions, and pull request guidelines.

## License

[MIT](../../LICENSE) © Felipe Sauer
