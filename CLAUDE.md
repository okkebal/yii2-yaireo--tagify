# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Repository

https://github.com/okkebal/yii2-yaireo--tagify

## What this is

A Yii2 extension package (`okkebal/yii2-tagify`) that wraps the [@yaireo/tagify](https://github.com/yairEO/tagify) v4 JavaScript library as a Yii2 `InputWidget`. It ships bundled JS/CSS assets so consumer apps need no npm step.

## Installation / local dev

```bash
composer require okkebal/yii2-tagify
```

For local development in a consumer app, reference this directory as a path repository in that app's `composer.json`. There are no build steps — the JS/CSS files in `src/assets/` are pre-built copies from @yaireo/tagify.

## Package structure

- `src/Tagify.php` — main `InputWidget`; builds Tagify JS config from PHP properties and registers inline JS
- `src/ActiveField.php` — extends `yii\widgets\ActiveField` with convenience methods (`tagify`, `tagifyTags`, `tagifySelect`, `tagifyMultiSelect`, `tagifyAjax`, `tagifyMix`)
- `src/TagifyAsset.php` — registers `tagify.js` + `tagify.css` from `src/assets/` via the `@tagify` alias
- `src/Bootstrap.php` — sets the `@tagify` alias at app bootstrap (registered via `extra.bootstrap` in `composer.json`)
- `src/assets/` — vendored tagify JS (UMD + ESM) and CSS; update these files when upgrading the upstream library

## Key design points

**Option precedence**: `clientOptions` array is merged last in `buildClientOptions()`, giving it the highest precedence over all convenience properties. Use it to pass raw Tagify options or `JsExpression` callbacks.

**CSV output**: When `outputFormat='csv'` (the default), the widget injects `originalInputValueFormat` as a JS function that produces a plain comma-separated string instead of Tagify's default JSON array. This is the easiest format for server-side handling.

**AJAX flow**: When `ajaxUrl` is set, `registerAjaxScript()` attaches an `input` event listener with a debounced `fetch()` call. The endpoint receives `?q=<typed>` and must return `["val1","val2"]` or `[{"value":"v","label":"L"}]`. The whitelist is mutated in-place on each response.

**Array model values**: The widget automatically `implode(',')`s array attribute values before rendering. The consumer model must handle the reverse on `afterFind`/`beforeValidate`.

**JS variable naming**: Each widget instance gets a unique JS variable named `tagify_<sanitized-id>` to avoid collisions when multiple widgets appear on one page.