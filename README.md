<p align="center">
  <img src="https://raw.githubusercontent.com/felipesauer/safeaccess-identum/main/.github/assets/logo.svg" width="80" alt="safeaccess-identum logo">
</p>

<h1 align="center">Safe Access Identum</h1>

<p align="center">
  Brazilian document validation — CPF, CNPJ, CNH, CEP, CNS, PIS, IE (all 27 states), RENAVAM, Mercosul Plate, Voter Title, Payment Card, PIX key, and Certificate. PHP &amp; TypeScript, identical API, zero production dependencies.
</p>

<p align="center">
  <a href="https://codecov.io/gh/felipesauer/safeaccess-identum"><img src="https://img.shields.io/codecov/c/github/felipesauer/safeaccess-identum?label=Coverage" alt="Coverage"></a>
  <a href="https://www.npmjs.com/package/@safeaccess/identum"><img src="https://img.shields.io/npm/v/@safeaccess/identum?label=npm" alt="npm"></a>
  <a href="https://packagist.org/packages/safeaccess/identum"><img src="https://img.shields.io/packagist/v/safeaccess/identum?label=packagist" alt="Packagist"></a>
  <a href="LICENSE"><img src="https://img.shields.io/badge/License-MIT-blue.svg" alt="License: MIT"></a>
  <img src="https://img.shields.io/badge/PHP-8.2%2B-777BB4?logo=php&logoColor=white" alt="PHP 8.2+">
  <img src="https://img.shields.io/badge/Node.js-22%2B-339933?logo=nodedotjs&logoColor=white" alt="Node.js 22+">
  <img src="https://img.shields.io/endpoint?url=https://gist.githubusercontent.com/felipesauer/270e797ff861330a4ac508ab7e9ce2bd/raw/infection-msi.json" alt="PHP Infection MSI">
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

> **Version 2.0.** `validate()` now returns a rich result object instead of a boolean, and there are new capabilities (`format`, `generate`, metadata) and document types. Upgrading from 1.x? See [Migrating from 1.x](#migrating-from-1x).

## Features

- **13 document types** — CPF, CNPJ (alphanumeric), CNH, CEP, CNS, PIS, IE (all 27 states), RENAVAM, Mercosul Plate, Voter Title, plus **Payment Card (Luhn), PIX key, and civil-registry Certificate**
- **PHP + TypeScript** — same public API, same checksum logic, same sanitization rules, same output for the same input
- **Rich result** — `validate()` returns `{ valid, reason, normalized, meta }`; `isValid()` is the boolean shortcut
- **Machine-readable reasons** — every failure carries a stable `ReasonCode` (`invalid_format`, `wrong_length`, `bad_check_digit`, `unknown_uf`, `known_invalid`, `denied`) — ideal as i18n keys
- **Metadata extraction** — offline, from the number itself: CPF/IE `uf`, CNS `type`, CNPJ `isMatriz`/`isAlphanumeric`, Card `brand`, PIX `keyType`, Certificate `type`
- **`format()` / `strip()`** — apply or remove the canonical mask (best-effort presentation helpers)
- **`generate()`** — produce valid documents for tests (`Identum::generateCpf()`, …)
- **IE all 27 states** — every state algorithm implemented, tested with edge cases in both packages
- **Input sanitization by default** — `'529.982.247-25'` and `'52998224725'` both just work
- **Allow list / deny list** — force-accept or force-reject specific values (format-agnostic: both input and list entries are sanitized before matching). Allow list takes precedence over deny list
- **Tree-shakeable (JS)** — per-document subpath exports (`@safeaccess/identum/cpf`) so you only bundle what you import
- **100% line & function coverage** (branches enforced at 99%+; the remainder are unreachable defensive guards) — Pest + Infection (PHP) · Vitest + Stryker (TypeScript), mutation MSI ≥ 85%
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

// Boolean shortcut — the quickest check
Identum::cpf('529.982.247-25')->isValid();                       // true
Identum::cnpj('84.773.274/0001-03')->isValid();                  // true
Identum::cnpj('A0000000000032')->isValid();                      // true — alphanumeric CNPJ
Identum::cnh('22522791508')->isValid();                          // true
Identum::cep('78000-000')->isValid();                            // true
Identum::cns('100000000060018')->isValid();                      // true
Identum::pis('329.9506.158-9')->isValid();                       // true
Identum::ie('343.173.196.450', StateEnum::SP)->isValid();        // true — all 27 states
Identum::renavam('60390908553')->isValid();                      // true
Identum::placa('ABC1D23')->isValid();                            // true — Mercosul format
Identum::tituloEleitor('123456781295')->isValid();               // true
Identum::cartao('4111111111111111')->isValid();                  // true — Luhn
Identum::pix('pix@bcb.gov.br')->isValid();                       // true — PIX key
Identum::certidao('00188301551987100018050000056665')->isValid();// true — certificate

// Rich result — why it failed, the normalized value, and extracted metadata
$result = Identum::cpf('529.982.247-25')->validate();
$result->valid;       // true
$result->reason;      // null (a ReasonCode enum when invalid)
$result->normalized;  // '52998224725'
$result->meta?->uf;   // 'SP' — fiscal region

$bad = Identum::cpf('529.982.247-24')->validate();
$bad->valid;          // false
$bad->reason;         // ReasonCode::BadCheckDigit

// Validate or throw — the exception carries structured context
try {
    Identum::cpf('000.000.000-00')->validateOrFail();
} catch (ValidationException $e) {
    $e->document;   // 'cpf'
    $e->reason;     // ReasonCode::KnownInvalid
    $e->normalized; // '00000000000'
}

// Allow list / deny list (format-agnostic: input and list entries are sanitized before matching)
Identum::cpf('529.982.247-25')->denyList(['52998224725'])->isValid();   // false — matches despite the mask
Identum::cpf('000.000.000-00')->allowList(['000.000.000-00'])->isValid(); // true

// Format / strip
Identum::cpf('52998224725')->format();     // '529.982.247-25'
Identum::cpf('529.982.247-25')->strip();   // '52998224725'

// Generate valid documents for tests
Identum::generateCpf();                     // e.g. '76502099010'
Identum::generateCnpj(formatted: true);     // e.g. '12.345.678/0001-95'
```

### TypeScript

```typescript
import { Identum, StateEnum, ValidationException } from '@safeaccess/identum';

// Boolean shortcut — the quickest check
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

const bad = Identum.cpf('529.982.247-24').validate();
bad.valid;          // false
bad.reason;         // 'bad_check_digit'

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

// Allow list / deny list (format-agnostic: input and list entries are sanitized before matching)
Identum.cpf('529.982.247-25').denyList(['52998224725']).isValid();   // false — matches despite the mask
Identum.cpf('000.000.000-00').allowList(['000.000.000-00']).isValid(); // true

// Format / strip
Identum.cpf('52998224725').format();     // '529.982.247-25'
Identum.cpf('529.982.247-25').strip();   // '52998224725'

// Generate valid documents for tests
Identum.generateCpf();                    // e.g. '76502099010'
Identum.generateCnpj(true);               // e.g. '12.345.678/0001-95'
```

## API

All validator classes share the same fluent interface after construction:

| Method | PHP return | TS return | Description |
| --- | --- | --- | --- |
| `validate()` | `ValidationResult` | `ValidationResult` | Rich result: `{ valid, reason, normalized, meta }` |
| `isValid()` | `bool` | `boolean` | Boolean shortcut for `validate().valid` |
| `validateOrFail()` | `void` | `void` | Throws `ValidationException` (carrying `document`, `reason`, `normalized`) when invalid |
| `format()` | `string` | `string` | Canonical mask applied, best-effort (returns the stripped value if it doesn't fit) |
| `strip()` | `string` | `string` | Canonical value with every mask character removed |
| `denyList(string[])` | `static` | `this` | Force-reject the given values regardless of checksum |
| `allowList(string[])` | `static` | `this` | Force-accept the given values regardless of checksum |
| `raw()` | `string` | `string` | The input exactly as provided |

`denyList()` and `allowList()` are fluent and can be chained before `validate()`. Matching is **format-agnostic** — both the input and the list entries are passed through the same sanitization the validator uses, so `'529.982.247-25'` matches a list entry of `'52998224725'`. When a value appears in both lists, **allow list wins**.

> `blacklist()` / `whitelist()` still work as deprecated aliases of `denyList()` / `allowList()` and will be removed in 3.0.

### The result object

```
validate() → {
  valid:      boolean       // did it pass?
  reason:     ReasonCode|null// why it failed (null when valid)
  normalized: string        // sanitized canonical value
  meta:       DocumentMeta|null // extracted info (null when there is none)
}
```

**Reason codes** (stable, `snake_case`, safe as i18n keys), in the order they are checked: `invalid_format` → `wrong_length` → `bad_check_digit` → `unknown_uf` → `known_invalid` → `denied`.

**Generators** — one per type on the facade: `generateCpf()`, `generateCnpj()`, `generateCnh()`, `generateCep()`, `generateCns()`, `generatePis()`, `generateIe(state)`, `generateRenavam()`, `generatePlaca()`, `generateTituloEleitor()`. They return unmasked values by default; pass `formatted: true` (PHP) / `true` (TS) where a mask exists. Every generated value passes its own validator.

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
| Payment Card    | `cartao`         | `CartaoValidation`         | `CartaoValidation`        |
| PIX key         | `pix`            | `PixValidation`            | `PixValidation`           |
| Certificate     | `certidao`       | `CertidaoValidation`       | `CertidaoValidation`      |

### IE — all 27 states

```php
use SafeAccess\Identum\Assets\IE\StateEnum;

Identum::ie('153189458', StateEnum::BA)->isValid();   // true — Mod-10/11 dual
Identum::ie('7908930932562', StateEnum::MG)->isValid(); // true — Mod-10 + Mod-11
Identum::ie('P199163724045', StateEnum::SP)->isValid(); // true — rural (P prefix)
```

```typescript
import { Identum, StateEnum } from '@safeaccess/identum';

Identum.ie('153189458', StateEnum.BA).isValid();     // true
Identum.ie('7908930932562', StateEnum.MG).isValid(); // true
Identum.ie('P199163724045', StateEnum.SP).isValid(); // true
```

All 27 states are tested with valid inputs, invalid checksums, wrong lengths, wrong prefixes, and modulus edge cases.

## CNPJ — alfanumérico

The CNPJ format supports alphanumeric characters in addition to numeric-only (Receita Federal 2026 format):

```php
Identum::cnpj('A0000000000032')->isValid(); // true — alphanumeric CNPJ
```

```typescript
Identum.cnpj('A0000000000032').isValid(); // true
```

## Payment Card, PIX and Certificate

```php
// Payment card — Luhn (Mod-10). Integrity only; does not check the card exists.
Identum::cartao('4111111111111111')->validate()->meta?->brand; // 'visa' (best-effort BIN)

// PIX — any of the five DICT key types; meta.keyType tells which one
Identum::pix('+5510998765432')->validate()->meta?->keyType;     // 'phone'
Identum::pix('pix@bcb.gov.br')->isValid();                      // true (email)
Identum::pix('52998224725')->isValid();                         // true (CPF key)

// Civil-registry certificate — 32-digit nationwide matrícula (Mod-11 ×10)
Identum::certidao('00188301551987100018050000056665')->validate()->meta?->type; // 'birth'
```

```typescript
Identum.cartao('4111111111111111').validate().meta?.brand;      // 'visa'
Identum.pix('+5510998765432').validate().meta?.keyType;         // 'phone'
Identum.certidao('00188301551987100018050000056665').validate().meta?.type; // 'birth'
```

> **Scope note.** Card validation is Luhn integrity plus best-effort brand detection — it does **not** prove a card exists or is authorized (that needs an online BIN/issuer lookup). PIX validates key **format** (and CPF/CNPJ checksums); it does not query the DICT. Both are offline checks by design.

## Direct instantiation

Use the validator classes directly when you don't need the facade:

```php
use SafeAccess\Identum\Assets\CPF\CPFValidation;

$validator = new CPFValidation('529.982.247-25');
$validator->isValid(); // true
```

```typescript
import { CPFValidation } from '@safeaccess/identum';

const validator = new CPFValidation('529.982.247-25');
validator.isValid(); // true
```

### Tree-shaking (JS)

Import a single validator through its subpath so bundlers drop everything else:

```typescript
import { CPFValidation } from '@safeaccess/identum/cpf'; // only CPF ends up in the bundle
```

Every document has a subpath (`/cpf`, `/cnpj`, `/ie`, `/cartao`, `/pix`, `/certidao`, …). The package is marked `sideEffects: false`. The root barrel (`@safeaccess/identum`) still re-exports everything for convenience.

## Migrating from 1.x

Version 2.0 reshapes the API and adds capabilities and document types. Both packages change in lockstep, so the same steps apply to PHP and TypeScript. Checksums, sanitization, and all 1.x documents are unchanged — a value valid in 1.x is still valid in 2.0.

| 1.x | 2.0 | Why |
| --- | --- | --- |
| `validate(): bool` | `validate(): ValidationResult` — use `isValid()` for a boolean | Rich result: reason, normalized value, metadata |
| `validateOrFail(): true` | `validateOrFail(): void`; exception carries `document` / `reason` / `normalized` | Structured errors |
| `blacklist()` / `whitelist()` | `denyList()` / `allowList()` (old names deprecated, removed in 3.0) | Clearer terminology |
| exception message `"cpf: input invalid"` | `"cpf: <reason>"` (e.g. `"cpf: bad_check_digit"`) | Message reflects the machine-readable reason |
| PHP `Identum::alias()` / `getAlias()` | removed — use the concrete static factories | Facade simplified, mirrors the JS one |
| PHP `CPFValidation::make(...)` | `new CPFValidation(...)` | The `make()` trait was removed |

**The one change that touches most codebases:** `validate()` now returns an object, which is **always truthy**. Replace `validate()` used as a boolean with `isValid()`:

```diff
- if (Identum::cpf($doc)->validate()) { /* ... */ }   // ⚠️ always truthy in 2.0
+ if (Identum::cpf($doc)->isValid())  { /* ... */ }
```
```diff
- if (Identum.cpf(doc).validate()) { /* ... */ }       // ⚠️ always truthy in 2.0
+ if (Identum.cpf(doc).isValid())  { /* ... */ }
```

**Checklist**

- [ ] Replace `.validate()` used as a boolean with `.isValid()`.
- [ ] Update any code that read the return value of `validateOrFail()` (now `void`).
- [ ] Rename `blacklist`/`whitelist` → `denyList`/`allowList` (or keep the deprecated aliases for now).
- [ ] If you parsed the exception message, switch to `$e->reason` (PHP) / `e.reason` (TS).
- [ ] (PHP) Replace `::make(...)` with `new ...(...)` and any `Identum::alias`/`getAlias` usage with the static factories.

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md) for development setup, commit conventions, and pull request guidelines.

## Security

See [SECURITY.md](SECURITY.md) for vulnerability reporting and the security policy.

## License

[MIT](LICENSE) © Felipe Sauer
