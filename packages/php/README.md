<p align="center">
  <img src="https://raw.githubusercontent.com/felipesauer/safeaccess-identum/main/.github/assets/logo.svg" width="80" alt="safeaccess-identum logo">
</p>

<h1 align="center">Safe Access Identum — PHP</h1>

PHP library for validating Brazilian documents — CPF, CNPJ, CNH, CEP, CNS, PIS, IE (all 27 states), RENAVAM, Mercosul Plate, Voter Title, Payment Card, PIX key, and Certificate. Input sanitization by default. Zero production dependencies.

<p align="center">
  <a href="https://packagist.org/packages/safeaccess/identum"><img src="https://img.shields.io/packagist/v/safeaccess/identum?label=packagist" alt="Packagist"></a>
  <a href="../../LICENSE"><img src="https://img.shields.io/badge/License-MIT-blue.svg" alt="License: MIT"></a>
  <img src="https://img.shields.io/badge/PHP-8.2%2B-777BB4?logo=php&amp;logoColor=white" alt="PHP 8.2+">
  <img src="https://img.shields.io/badge/PHPStan-max-0A6DAD" alt="PHPStan max">
  <img src="https://img.shields.io/badge/Tested%20with-Pest-FF5733" alt="Tested with Pest">
  <img src="https://img.shields.io/endpoint?url=https://gist.githubusercontent.com/felipesauer/80c602b17107f88fb17794d4d44c94fa/raw/infection-msi.json" alt="Infection MSI">
</p>

---

> **Version 2.0.** `validate()` now returns a rich result object instead of a boolean, with new capabilities (`format`, `generate`, metadata) and document types. Upgrading from 1.x? See [Migrating from 1.x](https://github.com/felipesauer/safeaccess-identum#migrating-from-1x).

## Features

- **13 document types** — CPF, CNPJ (alphanumeric), CNH, CEP, CNS, PIS, IE (all 27 states), RENAVAM, Mercosul Plate, Voter Title, plus **Payment Card (Luhn), PIX key, and civil-registry Certificate**
- **Rich result** — `validate()` returns `{ valid, reason, normalized, meta }`; `isValid()` is the boolean shortcut
- **Machine-readable reasons** — every failure carries a stable `ReasonCode` (`invalid_format`, `wrong_length`, `bad_check_digit`, `unknown_uf`, `known_invalid`, `denied`)
- **Metadata extraction** — offline, from the number: CPF/IE `uf`, CNS `type`, CNPJ `isMatriz`/`isAlphanumeric`, Card `brand`, PIX `keyType`, Certificate `type`
- **`format()` / `strip()`** — apply or remove the canonical mask
- **`generate()`** — valid documents for tests (`Identum::generateCpf()`, …)
- **IE all 27 states** — every state algorithm implemented and tested with edge cases
- **Input sanitization by default** — `'529.982.247-25'` and `'52998224725'` both just work
- **Allow list / deny list** — force-accept or force-reject specific values (allow list wins)
- **100% line + branch coverage** — tested with Pest 3 · Infection mutation testing (≥ 85% MSI)
- **Zero production dependencies** — pure PHP 8.2+

## The problem

Validating Brazilian documents in PHP accumulates silently: scattered regexes, copy-pasted Mod-11 loops, and 27 state-specific IE algorithms scattered across the codebase. Each re-implementation gets Bahia's dual-modulus branch wrong and ships with no edge-case tests.

**Without this library:**

```php
function validateCpf(string $cpf): bool {
    $cpf = preg_replace('/\D/', '', $cpf);
    if (strlen($cpf) !== 11 || preg_match('/(\d)\1{10}/', $cpf)) return false;
    // 30+ lines: loops, hardcoded weights, manual digit comparison...
}
```

**With this library:**

```php
Identum::cpf('529.982.247-25')->isValid();                    // true
Identum::ie('343.173.196.450', StateEnum::SP)->isValid();     // true — all 27 states
```

## Installation

```bash
composer require safeaccess/identum
```

**Requirements:** PHP 8.2+

## Quick start

```php
use SafeAccess\Identum\Identum;
use SafeAccess\Identum\Assets\IE\StateEnum;
use SafeAccess\Identum\Exceptions\ValidationException;

// Boolean shortcut — formatting stripped automatically
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

// Validate or throw — the exception carries structured context
try {
    Identum::cpf('000.000.000-00')->validateOrFail();
} catch (ValidationException $e) {
    $e->document;   // 'cpf'
    $e->reason;     // ReasonCode::KnownInvalid
    $e->normalized; // '00000000000'
}

// Allow list / deny list (format-agnostic)
Identum::cpf('529.982.247-25')->denyList(['52998224725'])->isValid();   // false
Identum::cpf('000.000.000-00')->allowList(['000.000.000-00'])->isValid(); // true

// Format / strip / generate
Identum::cpf('52998224725')->format();      // '529.982.247-25'
Identum::cpf('529.982.247-25')->strip();    // '52998224725'
Identum::generateCpf();                      // e.g. '76502099010'
Identum::generateCnpj(formatted: true);      // e.g. '12.345.678/0001-95'
```

## Direct instantiation

```php
use SafeAccess\Identum\Assets\CPF\CPFValidation;

$validator = new CPFValidation('529.982.247-25');
$validator->isValid(); // true
```

## API

All validator classes share the same fluent interface after construction:

| Method | Return | Description |
| --- | --- | --- |
| `validate()` | `ValidationResult` | Rich result: `{ valid, reason, normalized, meta }` |
| `isValid()` | `bool` | Boolean shortcut for `validate()->valid` |
| `validateOrFail()` | `void` | Throws `ValidationException` (with `document`, `reason`, `normalized`) when invalid |
| `format()` | `string` | Canonical mask applied, best-effort |
| `strip()` | `string` | Canonical value with mask characters removed |
| `denyList(string[])` | `static` | Force-reject the specified values regardless of checksum |
| `allowList(string[])` | `static` | Force-accept the specified values regardless of checksum |
| `raw()` | `string` | The input exactly as provided |

> `blacklist()` / `whitelist()` still work as deprecated aliases of `denyList()` / `allowList()` and will be removed in 3.0.

**Reason codes** (stable, `snake_case`), in the order they are checked: `invalid_format` → `wrong_length` → `bad_check_digit` → `unknown_uf` → `known_invalid` → `denied`.

**Generators** — one per type on the facade: `generateCpf()`, `generateCnpj()`, `generateCnh()`, `generateCep()`, `generateCns()`, `generatePis()`, `generateIe($state)`, `generateRenavam()`, `generatePlaca()`, `generateTituloEleitor()`. Unmasked by default; pass `formatted: true` where a mask exists.

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

```php
use SafeAccess\Identum\Assets\IE\StateEnum;

Identum::ie('153189458', StateEnum::BA)->isValid();    // Bahia — Mod-10/11 dual
Identum::ie('7908930932562', StateEnum::MG)->isValid(); // Minas Gerais
Identum::ie('P199163724045', StateEnum::SP)->isValid(); // São Paulo rural (P prefix)
```

### CNPJ — alfanumérico

```php
Identum::cnpj('A0000000000032')->isValid(); // true — alphanumeric CNPJ
```

### Payment Card, PIX and Certificate

```php
// Payment card — Luhn integrity only; meta.brand is best-effort BIN detection
Identum::cartao('4111111111111111')->validate()->meta?->brand; // 'visa'

// PIX — any of the five DICT key types; meta.keyType tells which
Identum::pix('+5510998765432')->validate()->meta?->keyType;    // 'phone'

// Civil-registry certificate — 32-digit matrícula (Mod-11 ×10)
Identum::certidao('00188301551987100018050000056665')->validate()->meta?->type; // 'birth'
```

> Card validation is Luhn integrity plus best-effort brand — it does **not** prove a card exists (that needs an online lookup). PIX validates key **format** (and CPF/CNPJ checksums), not DICT registration. Both are offline by design.

## Contributing

See [CONTRIBUTING.md](../../CONTRIBUTING.md) for development setup, commit conventions, and pull request guidelines.

## License

[MIT](../../LICENSE) © Felipe Sauer
