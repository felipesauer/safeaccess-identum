# Changelog

## [0.2.0](https://github.com/felipesauer/safeaccess-identum/compare/js-v0.1.1...js-v0.2.0) (2026-07-10)


### ⚠ BREAKING CHANGES

* validate() returns an object instead of a boolean; use isValid() for the previous boolean behavior. validateOrFail() returns void. blacklist()/whitelist() are deprecated in favor of denyList()/allowList(). The PHP dynamic facade resolver (Identum::alias/getAlias) has been removed. See the README "Migrating from 1.x" section.

### Features

* 2.0 — rich validation API, new documents, format/generate, tree-shaking ([#54](https://github.com/felipesauer/safeaccess-identum/issues/54)) ([5005279](https://github.com/felipesauer/safeaccess-identum/commit/500527982a89b613e477f71e00ff4046356f7651))

## [0.1.1](https://github.com/felipesauer/safeaccess-identum/compare/js-v0.1.0...js-v0.1.1) (2026-06-08)


### Features

* enforce real PHP/JS parity, format-agnostic allow/deny lists, typed errors ([#33](https://github.com/felipesauer/safeaccess-identum/issues/33)) ([57cb356](https://github.com/felipesauer/safeaccess-identum/commit/57cb356c97f6f27bc07262c839bfea33cdac4b50))

## 0.1.0 (2026-04-14)


### Features

* **js:** initial public release of @safeaccess/identum ([f95a535](https://github.com/felipesauer/safeaccess-identum/commit/f95a53504ad3223834cbdf1e825e37b309cd4b77))
