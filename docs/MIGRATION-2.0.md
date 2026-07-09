# Migration guide — 1.x → 2.0

Version 2.0 reshapes the validation API and adds capabilities and document types.
Both packages (`safeaccess/identum` and `@safeaccess/identum`) change in lockstep,
so the same steps apply to PHP and TypeScript.

The single change that touches almost every codebase is **`validate()` now returns
an object instead of a boolean**. If you only need the old behavior, the mechanical
fix is `validate()` → `isValid()`.

---

## At a glance

| 1.x | 2.0 | Why |
| --- | --- | --- |
| `validate(): bool` | `validate(): ValidationResult` — use `isValid()` for a boolean | Rich result: reason, normalized value, metadata |
| `validateOrFail(): true` | `validateOrFail(): void`; exception carries `document` / `reason` / `normalized` | Structured errors |
| `blacklist()` / `whitelist()` | `denyList()` / `allowList()` (old names deprecated) | Clearer, modern terminology |
| exception message `"cpf: input invalid"` | `"cpf: <reason>"` (e.g. `"cpf: bad_check_digit"`) | Message reflects the machine-readable reason |
| PHP `Identum::alias()` / `getAlias()` (dynamic resolver) | removed — use the concrete static factories | Facade simplified, now mirrors the JS one |
| PHP `CPFValidation::make(...)` | `new CPFValidation(...)` | The `make()` trait was removed |

Nothing about the checksums, sanitization, or the supported 1.x documents changed —
a value that was valid in 1.x is still valid in 2.0.

---

## 1. `validate()` returns a result object

**Before:**

```php
if (Identum::cpf($doc)->validate()) { /* valid */ }
```
```ts
if (Identum.cpf(doc).validate()) { /* valid */ }
```

**After — quickest fix (`isValid()`):**

```php
if (Identum::cpf($doc)->isValid()) { /* valid */ }
```
```ts
if (Identum.cpf(doc).isValid()) { /* valid */ }
```

> ⚠️ `if (validate())` silently "works" but is now **always truthy** (an object is
> truthy), so every input would look valid. Search your codebase for `.validate()`
> used directly in a boolean position and switch those to `isValid()`.

**After — using the richer result** where it helps:

```php
$r = Identum::cpf($doc)->validate();
$r->valid;       // bool
$r->reason;      // ReasonCode|null
$r->normalized;  // string (sanitized)
$r->meta?->uf;   // metadata, when available
```
```ts
const r = Identum.cpf(doc).validate();
r.valid;        // boolean
r.reason;       // ReasonCode | null
r.normalized;   // string
r.meta?.uf;     // metadata, when available
```

## 2. `validateOrFail()` returns void and throws a richer exception

**Before:**

```php
$ok = Identum::cpf($doc)->validateOrFail(); // returned true, or threw
```

**After** — it returns nothing; catch the exception for the details:

```php
try {
    Identum::cpf($doc)->validateOrFail();
} catch (ValidationException $e) {
    $e->document;   // 'cpf'
    $e->reason;     // ReasonCode enum
    $e->normalized; // sanitized value
}
```
```ts
try {
    Identum.cpf(doc).validateOrFail();
} catch (e) {
    if (e instanceof ValidationException) {
        e.document; e.reason; e.normalized;
    }
}
```

If you relied on the return value (`$ok = ...->validateOrFail()`), drop the
assignment — reaching the next line already means success.

The exception **message** changed from the fixed `"cpf: input invalid"` to
`"cpf: <reason>"` (e.g. `"cpf: bad_check_digit"`). Prefer branching on
`$e->reason` over parsing the message.

## 3. `blacklist()` / `whitelist()` → `denyList()` / `allowList()`

Rename for clarity. The behavior (format-agnostic matching, allow list wins) is
unchanged.

```diff
- Identum::cpf($doc)->blacklist(['52998224725'])->validate();
+ Identum::cpf($doc)->denyList(['52998224725'])->isValid();

- Identum::cpf($doc)->whitelist(['000.000.000-00'])->validate();
+ Identum::cpf($doc)->allowList(['000.000.000-00'])->isValid();
```

The old names still work as **deprecated aliases** and will be removed in 3.0, so
you can rename gradually.

## 4. Reason codes

Failures now expose a stable, machine-readable code — ideal as an i18n key. The
codes, in the order they are evaluated:

`invalid_format` → `wrong_length` → `bad_check_digit` → `unknown_uf` →
`known_invalid` → `denied`

- **PHP:** a `ReasonCode` enum (`ReasonCode::BadCheckDigit`, with `->value === 'bad_check_digit'`).
- **TS:** a `ReasonCode` string-literal union (`'bad_check_digit'`) plus a `ReasonCode` const object.

The string values are identical across both languages.

## 5. PHP: facade resolver removed

The dynamic alias resolver was removed in favor of concrete static factories
(matching the TypeScript facade). If you used the internal resolver API:

```diff
- Identum::getAlias('cpf');       // removed
- Identum::alias('cpf', MyClass::class); // removed
+ Identum::cpf($doc);             // just call the factory
```

Direct instantiation lost the `make()` helper:

```diff
- CPFValidation::make('529.982.247-25');
+ new CPFValidation('529.982.247-25');
```

`Internal\AbstractResolver`, `Internal\Aliasable`, and `Internal\Makeable` no
longer exist. (These were `@internal`; most consumers never touched them.)

---

## What's new in 2.0 (optional to adopt)

None of these require changes — they are additive.

- **New documents:** Payment Card (`cartao`, Luhn), PIX key (`pix`), civil-registry
  Certificate (`certidao`).
- **`format()` / `strip()`** — apply or remove a document's canonical mask.
- **`generate()`** — valid documents for tests: `Identum::generateCpf()`, etc.
- **Metadata** via `validate().meta` — CPF/IE `uf`, CNS `type`, CNPJ
  `isMatriz`/`isAlphanumeric`, Card `brand`, PIX `keyType`, Certificate `type`.
- **Tree-shaking (JS):** per-document subpath imports, e.g.
  `import { CPFValidation } from '@safeaccess/identum/cpf'`.

---

## Quick checklist

- [ ] Replace `.validate()` used as a boolean with `.isValid()`.
- [ ] Update any code that read the return value of `validateOrFail()`.
- [ ] Rename `blacklist`/`whitelist` → `denyList`/`allowList` (or keep the aliases for now).
- [ ] If you parsed the exception message, switch to `$e->reason`.
- [ ] (PHP) Replace `::make(...)` with `new ...(...)` and any `Identum::alias/getAlias` usage.
