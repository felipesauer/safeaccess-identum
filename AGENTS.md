# Agent rules for this repository

safeaccess-identum validates Brazilian documents (CPF, CNPJ, CNH, CEP, CNS, PIS, IE, RENAVAM, plates, cards, PIX keys and others).
It is an npm workspaces monorepo with two packages that expose the same API: `packages/js` (`@safeaccess/identum`, TypeScript,
Node 22+) and `packages/php` (`safeaccess/identum`, PHP 8.2+). Each rule below names where the repository enforces it; "(no check)"
means nothing but review enforces it.

## Commands

- JS, from `packages/js` (run `npm install` once at the repo root first): `npm run build`, `npm test`, `npm run typecheck`,
  `npm run lint`, `npm run test:parity`, `npm run test:coverage`, `npm run test:mutation`.
- PHP, from `packages/php` (`composer install` first): `composer test` (Pest, `--min=100 --strict-coverage`, needs pcov or Xdebug;
  `composer test:coverage` forces pcov), `composer analyse` (PHPStan), `composer format` (php-cs-fixer), `composer test:parity`,
  `composer test:mutation`.
- Formatting, from the repo root: `npx prettier --check . --ignore-path .gitignore --ignore-path .prettierignore` (use `--write` to fix).
- Before you commit a change, run the gates of the package you touched; a change to both packages runs both sets. Gates for the
  whole repo, in the order CI runs them: format, build, test with coverage, typecheck, parity, lint, mutation.

## Rules

1. **Both packages keep the same behavior.** A behavior change in `packages/js/src` or `packages/php/src` is made in the other one in
   the same pull request. The shared ground truth is `parity-vectors.json` at the repository root, read by both
   `packages/js/tests/parity.test.ts` and `packages/php/tests/Unit/ParityTest.php`: a new case goes into that file, not into one
   language's tests. A pull request that changes only `parity-vectors.json` triggers neither CI workflow (path filters), so run both parity
   commands yourself. Check: `npm run test:parity` and `composer test:parity`, both in CI (the Node 24 and PHP 8.4 "full" jobs).
2. **Coverage floors are intentional, and they are not the same in the two packages.** PHP: 100% (`--min=100` in `composer test`).
   JS (`packages/js/vitest.config.ts`): lines, functions and statements 100, **branches 99**, with the reason written next to the
   number (unreachable `?? null` guards and RNG-dependent `dv >= 10` arms). Do not raise the 99 by adding contrived tests, and do not
   lower any threshold or exclude a file to get green. Check: `npm test -- --coverage` (JS CI) and
   `vendor/bin/pest --coverage --min=100` (PHP CI).
3. **Mutation floors differ too.** PHP `minMsi` and `minCoveredMsi` are 100 (`packages/php/infection.json5`); JS Stryker breaks at 88
   (`thresholds.break` in `packages/js/stryker.config.json`). The CI step names match these numbers today; if they ever differ, the
   config files are the truth. Check: `bash scripts/mutation.sh` in `packages/php` and `npm run test:mutation` in
   `packages/js`; JS mutation runs on pull requests as well as on `main`.
4. **JS types and lint.** `strict`, `noImplicitAny`, `noImplicitReturns`, `exactOptionalPropertyTypes` (`packages/js/tsconfig.json`);
   ESLint from the root `eslint.config.js`. Check: `npm run typecheck` and `npm run lint` (JS CI, Node 24).
5. **PHP static analysis and style.** PHPStan level 9 with bleeding edge (`packages/php/phpstan.neon`, no `ignoreErrors` today: do not
   add any); PSR-12 through php-cs-fixer. Check: `vendor/bin/phpstan analyse` and `vendor/bin/php-cs-fixer fix --dry-run --diff`
   (PHP CI, PHP 8.4 full).
6. **Formatting.** Prettier, 4 spaces, single quotes, trailing commas, 100 columns (`.prettierrc.json`, `.editorconfig`); the whole
   repository is checked. Check: `.github/workflows/format.yml`. `.lintstagedrc.json` fixes staged files, but no pre-commit hook
   runs it in this repository (`.husky/` holds only `commit-msg`), so run the format check yourself before you commit.
7. **Commit messages and PR titles are Conventional Commits.** `<type>(<scope>): <description>`, scope from
   `js, php, docs, ci, deps` (`commitlint.config.js`: a scope outside the list is an error, a missing scope is a warning). Merges are squash-only, so the
   pull request title becomes the commit on `main`. Check: `.husky/commit-msg` locally (installed by `npm install` through the
   `prepare: husky` script) and `.github/workflows/commitlint.yml` on the PR title (bot PRs are skipped).
8. **Do not edit generated or signed files.** `CHANGELOG.md` files and `.release-please-manifest.json` come from release-please;
   versions are bumped by its release PR, not by hand. Nothing under `.mnema/` is reformatted or edited by hand (the bytes are signed;
   it is in `.prettierignore`). (no check)
9. **Do not reformat `packages/php/infection.json5`.** It is in `.prettierignore` so it stays identical to its twin in
   `safeaccess-inline`.
10. **Per-document code.** One folder per document under `packages/js/src/assets/<doc>` and `packages/php/src/Assets/<Doc>`; a new
    or changed validator is changed in both, with vectors in `parity-vectors.json`. (no check beyond rule 1)
11. **No production dependencies**; put new tooling in `devDependencies` / `require-dev`. (no check)
12. **Branches and pull requests.** Branch from `main` with a descriptive name (`feat/my-feature`); open the pull request against
    `main`. Review ownership is in `.github/CODEOWNERS` (a ruleset on `main` requires one approving review; code-owner review is not required). (no check beyond the ruleset)

## Never

- Commit secrets, tokens or `.env` files. Security reports go through `SECURITY.md`, not a public issue.
- Edit `.github/workflows/` to loosen a gate (lower a threshold, drop a step, widen a path filter) as part of an unrelated change.
- Bypass the hooks (`--no-verify`) or open a pull request with a red gate.
- Edit one package's tests to make a parity test pass; fix the divergent behavior.

## Where things live

- `packages/js/src`, `packages/php/src`: `identum.ts` / `Identum.php` (entry points), `assets/` / `Assets/` (one folder per document),
  `contracts/`, `exceptions/`. Tests: `packages/js/tests` and `packages/php/tests/Unit`.
- `parity-vectors.json` (root): the cross-language vectors.
- `.github/workflows/`: `js-ci.yml`, `php-ci.yml`, `format.yml`, `commitlint.yml`, `release.yml`, `split-packages.yml`.
- Humans' guide: `CONTRIBUTING.md`. Sibling repository with the same layout and tooling: `safeaccess-inline`.
