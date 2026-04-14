<p align="center">
  <img src="https://raw.githubusercontent.com/felipesauer/safeaccess-identum/main/.github/assets/logo.svg" width="80" alt="safeaccess-identum logo">
</p>

<h1 align="center">Safe Access Identum — PHP</h1>

PHP library for validating Brazilian documents — CPF, CNPJ, CNH, CEP, CNS, PIS, IE (all 27 states), RENAVAM, Mercosul Plate, and Voter Title. Input sanitization by default. Zero production dependencies.

<p align="center">
  <a href="https://packagist.org/packages/safeaccess/identum"><img src="https://img.shields.io/packagist/v/safeaccess/identum?label=packagist" alt="Packagist"></a>
  <a href="../../LICENSE"><img src="https://img.shields.io/badge/License-MIT-blue.svg" alt="License: MIT"></a>
  <img src="https://img.shields.io/badge/PHP-8.2%2B-777BB4?logo=php&amp;logoColor=white" alt="PHP 8.2+">
  <img src="https://img.shields.io/badge/PHPStan-max-0A6DAD" alt="PHPStan max">
  <img src="https://img.shields.io/badge/Tested%20with-Pest-FF5733" alt="Tested with Pest">
  <img src="https://img.shields.io/endpoint?url=https://gist.githubusercontent.com/felipesauer/80c602b17107f88fb17794d4d44c94fa/raw/infection-msi.json" alt="Infection MSI">
</p>

---

## Features

- **10 document types** — CPF, CNPJ (alphanumeric), CNH, CEP, CNS, PIS, IE (all 27 states), RENAVAM, Mercosul Plate, Voter Title
- **IE all 27 states** — every state algorithm implemented and tested with edge cases
- **Input sanitization by default** — `'529.982.247-25'` and `'52998224725'` both just work
- **`validateOrFail()`** — throws `ValidationException` instead of returning `false`
- **Blacklist / whitelist** — force-accept or force-reject specific values
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
Identum::cpf('529.982.247-25')->validate();                    // true
Identum::ie('343.173.196.450', StateEnum::SP)->validate();     // true — all 27 states
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

// All document types — formatting stripped automatically
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

## Direct instantiation

```php
use SafeAccess\Identum\Assets\CPF\CPFValidation;

$validator = CPFValidation::make('529.982.247-25');
$validator->validate(); // true
```

## API

All validator classes share the same fluent interface after construction:

```php
$v = Identum::cpf('52998224725'); // or CPFValidation::make('52998224725')

$v->validate();                                  // bool
$v->validateOrFail();                            // bool — throws ValidationException if invalid
$v->blacklist(['52998224725'])->validate();       // bool — force-reject these values
$v->whitelist(['00000000000'])->validate();       // bool — force-accept these values
```

| Method | Return | Description |
| --- | --- | --- |
| `validate()` | `bool` | Returns `true` if valid, `false` otherwise |
| `validateOrFail()` | `bool` | Returns `true` if valid, throws `ValidationException` otherwise |
| `blacklist(string[])` | `static` | Force-reject the specified values regardless of checksum |
| `whitelist(string[])` | `static` | Force-accept the specified values regardless of checksum |

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

```php
use SafeAccess\Identum\Assets\IE\StateEnum;

Identum::ie('153189458', StateEnum::BA)->validate();    // Bahia — Mod-10/11 dual
Identum::ie('7908930932562', StateEnum::MG)->validate(); // Minas Gerais
Identum::ie('P199163724045', StateEnum::SP)->validate(); // São Paulo rural (P prefix)
```

### CNPJ — alfanumérico

```php
Identum::cnpj('A0000000000032')->validate(); // true — alphanumeric CNPJ
```

## Contributing

See [CONTRIBUTING.md](../../CONTRIBUTING.md) for development setup, commit conventions, and pull request guidelines.

## License

[MIT](../../LICENSE) © Felipe Sauer
