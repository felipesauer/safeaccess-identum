<p align="center">
  <img src="https://raw.githubusercontent.com/felipesauer/safeaccess-identum/main/.github/assets/logo.svg" width="80" alt="safeaccess-identum logo">
</p>

<h1 align="center">Safe Access Identum</h1>

<p align="center">
  Brazilian document validation — CPF, CNPJ, CNH, CEP, CNS, PIS, IE (all 27 states), RENAVAM, Mercosul Plate, and Voter Title. PHP &amp; TypeScript, identical API, zero production dependencies.
</p>

<p align="center">
  <a href="https://codecov.io/gh/felipesauer/safeaccess-identum"><img src="https://img.shields.io/codecov/c/github/felipesauer/safeaccess-identum?label=Coverage" alt="Coverage"></a>
  <a href="https://www.npmjs.com/package/@safeaccess/identum"><img src="https://img.shields.io/npm/v/@safeaccess/identum?label=npm" alt="npm"></a>
  <a href="https://packagist.org/packages/safeaccess/identum"><img src="https://img.shields.io/packagist/v/safeaccess/identum?label=packagist" alt="Packagist"></a>
  <a href="LICENSE"><img src="https://img.shields.io/badge/License-MIT-blue.svg" alt="License: MIT"></a>
  <img src="https://img.shields.io/badge/PHP-8.2%2B-777BB4?logo=php&logoColor=white" alt="PHP 8.2+">
  <img src="https://img.shields.io/badge/Node.js-22%2B-339933?logo=nodedotjs&logoColor=white" alt="Node.js 22+">
  <img src="https://img.shields.io/endpoint?url=https://gist.githubusercontent.com/felipesauer/80c602b17107f88fb17794d4d44c94fa/raw/infection-msi.json" alt="PHP Infection MSI">
  <img src="https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Ffelipesauer%2Fsafeaccess-identum%2Fmain" alt="JS Stryker MSI">
</p>

---

## The problem

Validating Brazilian documents — CPF, CNPJ, IE — in production code accumulates silently: scattered regexes, copy-pasted checksum loops, and state-specific IE rules duplicated across the codebase. Each developer re-implements the same Mod-11 calculations, gets the Bahia dual-modulus branch wrong, and ships with no tests for edge cases like all-same-digit inputs or the CNPJ alfanumérico format.

**Without this library:**

```php
// PHP — CPF validation copy-pasted from StackOverflow
function validateCpf(string $cpf): bool {
    $cpf = preg_replace('/\D/', '', $cpf);
    if (strlen($cpf) !== 11 || preg_match('/(\d)\1{10}/', $cpf)) return false;
    // 30 lines of Mod-11 loops, weights hardcoded, DVs compared manually...
}
```

**With this library:**

```php
Identum::cpf('529.982.247-25')->validate(); // true — formatting stripped automatically
Identum::ie('343.173.196.450', StateEnum::SP)->validate(); // true — all 27 states
```

The same API in TypeScript, identical output for identical input.

## When to use this — and when not to

**Use this library when you need to validate Brazilian document numbers:** form submissions, API payloads, database entries, webhook data.

**Don't use it as a source of business rules you can't inspect.** All validation algorithms are open, documented, and unit-tested — so you can audit exactly what's being checked. If a government specification changes, open an issue.

## Features

- **10 document types** — CPF, CNPJ (alphanumeric), CNH, CEP, CNS, PIS, IE (all 27 states), RENAVAM, Mercosul Plate, Voter Title
- **PHP + TypeScript** — same public API, same checksum logic, same sanitization rules, same output for the same input
- **IE all 27 states** — every state algorithm implemented, tested with edge cases in both packages
- **Input sanitization by default** — `'529.982.247-25'` and `'52998224725'` both just work
- **`validateOrFail()`** — throws a typed `ValidationException` instead of returning `false`
- **Blacklist / whitelist** — force-accept or force-reject specific values, useful for test environments and exceptional business rules
- **100% line + branch coverage** — Pest + Infection (PHP) · Vitest + Stryker (TypeScript)
- **Zero production dependencies**

## Packages

| Package                                | Language         | Install                                |
| -------------------------------------- | ---------------- | -------------------------------------- |
| [`safeaccess/identum`](packages/php/)  | PHP 8.2+         | `composer require safeaccess/identum`  |
| [`@safeaccess/identum`](packages/js/)  | TypeScript (ESM) | `npm install @safeaccess/identum`      |

Both packages expose the same public API surface and are tested for behavioral parity.

## Installation

### PHP

```bash
composer require safeaccess/identum
```

**Requirements:** PHP 8.2+

### TypeScript

```bash
npm install @safeaccess/identum
```

**Requirements:** Node.js 22+

## Quick start

### PHP

```php
use SafeAccess\Identum\Identum;
use SafeAccess\Identum\Assets\IE\StateEnum;
use SafeAccess\Identum\Exceptions\ValidationException;

// All document types
Identum::cpf('529.982.247-25')->validate();                      // true
Identum::cnpj('84.773.274/0001-03')->validate();                 // true
Identum::cnpj('A0000000000032')->validate();                     // true — alphanumeric CNPJ
Identum::cnh('22522791508')->validate();                         // true
Identum::cep('78000-000')->validate();                           // true
Identum::cns('100000000060018')->validate();                     // true
Identum::pis('329.9506.158-9')->validate();                      // true
Identum::ie('343.173.196.450', StateEnum::SP)->validate();       // true — all 27 states
Identum::renavam('60390908553')->validate();                     // true
Identum::placa('ABC1D23')->validate();                           // true — Mercosul format
Identum::tituloEleitor('123456781295')->validate();              // true

// Validate or throw
try {
    Identum::cpf('000.000.000-00')->validateOrFail();
} catch (ValidationException $e) {
    // handle invalid document
}

// Blacklist / whitelist
Identum::cpf('529.982.247-25')->blacklist(['529.982.247-25'])->validate(); // false
Identum::cpf('000.000.000-00')->whitelist(['000.000.000-00'])->validate(); // true
```

### TypeScript

```typescript
import { Identum, StateEnum, ValidationException } from '@safeaccess/identum';

// All document types
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

## API

All validator classes share the same fluent interface after construction:

| Method | PHP return | TS return | Description |
| --- | --- | --- | --- |
| `validate()` | `bool` | `boolean` | Returns `true` if valid, `false` otherwise |
| `validateOrFail()` | `bool` | `boolean` | Returns `true` if valid, throws `ValidationException` otherwise |
| `blacklist(string[])` | `static` | `this` | Force-reject the given values regardless of checksum |
| `whitelist(string[])` | `static` | `this` | Force-accept the given values regardless of checksum |

`blacklist()` and `whitelist()` are fluent and can be chained before `validate()` or `validateOrFail()`.

## Supported documents

| Document        | Alias            | PHP Class                  | TS Class                  |
| --------------- | ---------------- | -------------------------- | ------------------------- |
| CPF             | `cpf`            | `CPFValidation`            | `CPFValidation`           |
| CNPJ            | `cnpj`           | `CNPJValidation`           | `CNPJValidation`          |
| CNH             | `cnh`            | `CNHValidation`            | `CNHValidation`           |
| CEP             | `cep`            | `CEPValidation`            | `CEPValidation`           |
| CNS             | `cns`            | `CNSValidation`            | `CNSValidation`           |
| PIS/PASEP       | `pis`            | `PISValidation`            | `PISValidation`           |
| IE              | `ie`             | `IEValidation`             | `IEValidation`            |
| RENAVAM         | `renavam`        | `RenavamValidation`        | `RenavamValidation`       |
| Mercosul Plate  | `placa`          | `PlateMercosulValidation`  | `PlateMercosulValidation` |
| Voter Title     | `tituloEleitor`  | `VoterTitleValidation`     | `VoterTitleValidation`    |

### IE — all 27 states

```php
use SafeAccess\Identum\Assets\IE\StateEnum;

Identum::ie('153189458', StateEnum::BA)->validate();   // true — Mod-10/11 dual
Identum::ie('7908930932562', StateEnum::MG)->validate(); // true — Mod-10 + Mod-11
Identum::ie('P199163724045', StateEnum::SP)->validate(); // true — rural (P prefix)
```

```typescript
import { Identum, StateEnum } from '@safeaccess/identum';

Identum.ie('153189458', StateEnum.BA).validate();     // true
Identum.ie('7908930932562', StateEnum.MG).validate(); // true
Identum.ie('P199163724045', StateEnum.SP).validate(); // true
```

All 27 states are tested with valid inputs, invalid checksums, wrong lengths, wrong prefixes, and modulus edge cases.

## CNPJ — alfanumérico

The CNPJ format supports alphanumeric characters in addition to numeric-only (Receita Federal 2026 format):

```php
Identum::cnpj('A0000000000032')->validate(); // true — alphanumeric CNPJ
```

```typescript
Identum.cnpj('A0000000000032').validate(); // true
```

## Direct instantiation

Use the validator classes directly when you don't need the facade:

```php
use SafeAccess\Identum\Assets\CPF\CPFValidation;

$validator = CPFValidation::make('529.982.247-25');
$validator->validate(); // true
```

```typescript
import { CPFValidation } from '@safeaccess/identum';

const validator = new CPFValidation('529.982.247-25');
validator.validate(); // true
```

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md) for development setup, commit conventions, and pull request guidelines.

## Security

See [SECURITY.md](SECURITY.md) for vulnerability reporting and the security policy.

## License

[MIT](LICENSE) © Felipe Sauer
