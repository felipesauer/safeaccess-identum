# Changelog

## [0.2.0](https://github.com/felipesauer/safeaccess-identum/compare/php-v0.1.8...php-v0.2.0) (2026-07-10)


### ⚠ BREAKING CHANGES

* validate() returns an object instead of a boolean; use isValid() for the previous boolean behavior. validateOrFail() returns void. blacklist()/whitelist() are deprecated in favor of denyList()/allowList(). The PHP dynamic facade resolver (Identum::alias/getAlias) has been removed. See the README "Migrating from 1.x" section.

### Features

* 2.0 — rich validation API, new documents, format/generate, tree-shaking ([#54](https://github.com/felipesauer/safeaccess-identum/issues/54)) ([5005279](https://github.com/felipesauer/safeaccess-identum/commit/500527982a89b613e477f71e00ff4046356f7651))

## [0.1.8](https://github.com/felipesauer/safeaccess-identum/compare/php-v0.1.7...php-v0.1.8) (2026-07-09)


### Miscellaneous Chores

* **php:** Bump friendsofphp/php-cs-fixer from 3.95.4 to 3.95.8 in /packages/php in the dev-dependencies group across 1 directory ([#42](https://github.com/felipesauer/safeaccess-identum/issues/42)) ([8b96c31](https://github.com/felipesauer/safeaccess-identum/commit/8b96c314b21a9248254f69bc05e9f5f3e8ff9edc))
* **php:** Bump the dev-dependencies group across 1 directory with 2 updates ([#52](https://github.com/felipesauer/safeaccess-identum/issues/52)) ([68dab21](https://github.com/felipesauer/safeaccess-identum/commit/68dab2142fb36b59a9992e0213f0c7b6e493fe82))

## [0.1.7](https://github.com/felipesauer/safeaccess-identum/compare/php-v0.1.6...php-v0.1.7) (2026-06-08)


### Miscellaneous Chores

* **php:** Bump the dev-dependencies group in /packages/php with 2 updates ([#39](https://github.com/felipesauer/safeaccess-identum/issues/39)) ([7ceaafe](https://github.com/felipesauer/safeaccess-identum/commit/7ceaafe6ba1a7b2dfa4638299cbbb59fbb6671f5))

## [0.1.6](https://github.com/felipesauer/safeaccess-identum/compare/php-v0.1.5...php-v0.1.6) (2026-06-08)


### Features

* enforce real PHP/JS parity, format-agnostic allow/deny lists, typed errors ([#33](https://github.com/felipesauer/safeaccess-identum/issues/33)) ([57cb356](https://github.com/felipesauer/safeaccess-identum/commit/57cb356c97f6f27bc07262c839bfea33cdac4b50))

## [0.1.5](https://github.com/felipesauer/safeaccess-identum/compare/php-v0.1.4...php-v0.1.5) (2026-06-03)


### Miscellaneous Chores

* **php:** Bump the dev-dependencies group in /packages/php with 2 updates ([#30](https://github.com/felipesauer/safeaccess-identum/issues/30)) ([e7b16ea](https://github.com/felipesauer/safeaccess-identum/commit/e7b16eac17d883fd2316f4fa2ff69afacfe80986))

## [0.1.4](https://github.com/felipesauer/safeaccess-identum/compare/php-v0.1.3...php-v0.1.4) (2026-05-26)


### Miscellaneous Chores

* **php:** Bump the dev-dependencies group in /packages/php with 2 updates ([#24](https://github.com/felipesauer/safeaccess-identum/issues/24)) ([d86fc3c](https://github.com/felipesauer/safeaccess-identum/commit/d86fc3c5e563e6ff8a71e4250073fc68af80aca8))

## [0.1.3](https://github.com/felipesauer/safeaccess-identum/compare/php-v0.1.2...php-v0.1.3) (2026-05-05)


### Miscellaneous Chores

* **php:** Bump phpstan/phpstan from 2.1.51 to 2.1.54 in /packages/php in the dev-dependencies group ([#17](https://github.com/felipesauer/safeaccess-identum/issues/17)) ([0db23e7](https://github.com/felipesauer/safeaccess-identum/commit/0db23e7ddcf95d0c7735f765ab35c7bbb2c9e94d))

## [0.1.2](https://github.com/felipesauer/safeaccess-identum/compare/php-v0.1.1...php-v0.1.2) (2026-04-28)


### Miscellaneous Chores

* **php:** Bump phpstan/phpstan from 2.1.50 to 2.1.51 in /packages/php in the dev-dependencies group ([#14](https://github.com/felipesauer/safeaccess-identum/issues/14)) ([02f4dae](https://github.com/felipesauer/safeaccess-identum/commit/02f4daec2853d490fff30009c3d65a67c3ca2fb9))

## [0.1.1](https://github.com/felipesauer/safeaccess-identum/compare/php-v0.1.0...php-v0.1.1) (2026-04-21)


### Miscellaneous Chores

* **php:** Bump phpstan/phpstan from 2.1.46 to 2.1.50 in /packages/php in the dev-dependencies group ([#9](https://github.com/felipesauer/safeaccess-identum/issues/9)) ([da7e0a8](https://github.com/felipesauer/safeaccess-identum/commit/da7e0a8f73556ac14fb2a2b4602e0871b2a423db))

## 0.1.0 (2026-04-14)


### Features

* **js:** initial public release of @safeaccess/identum ([f95a535](https://github.com/felipesauer/safeaccess-identum/commit/f95a53504ad3223834cbdf1e825e37b309cd4b77))


### Miscellaneous Chores

* **main:** release/v0.1.0 ([#1](https://github.com/felipesauer/safeaccess-identum/issues/1)) ([78a9cf4](https://github.com/felipesauer/safeaccess-identum/commit/78a9cf4e6e223f33a60758e4912239aad990d4d8))
* **php:** Bump friendsofphp/php-cs-fixer from 3.94.2 to 3.95.1 in /packages/php in the dev-dependencies group ([#3](https://github.com/felipesauer/safeaccess-identum/issues/3)) ([334f917](https://github.com/felipesauer/safeaccess-identum/commit/334f9171abbf420f6142710cbbd4e0e344a403a8))
