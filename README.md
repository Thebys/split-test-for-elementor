# Split Test For Elementor (Community Fork)

An updated fork of **[Split Test For Elementor](https://wordpress.org/plugins/split-test-for-elementor/)**, with critical bug fixes, security patches, and the pro-version paywall removed.

> **Note:** This fork is based on v1.8.4 of the original plugin. All credit for the core concept and implementation goes to Rocket Elements / novacreator. This fork exists solely because the original plugin appears to have limited maintenance activity, and the bugs below were blocking production use.

## Why This Fork?

The original plugin is a solid, well-designed A/B testing solution that works natively inside Elementor — no external services, no subscription fees. However, in production use we encountered several critical issues that needed fixing:

- **Template-loaded tests were broken** — tests placed via Elementor templates never resolved correctly
- **SQL injection vulnerabilities** — multiple database queries used raw string concatenation
- **Unreliable variation distribution** — low-precision random range caused skewed results with fractional percentages
- **Null reference errors** — missing guards caused PHP fatals under certain conditions
- **JavaScript bugs** — typos in variable references broke client-side test execution
- **Admin form bugs** — unable to save URL values for 3+ variations in URL-type tests

## What Changed

### Security Fixes

- **SQL injection patches** — all raw query concatenations replaced with `$wpdb->prepare()` across `ConversionTracker`, `TestRepo`, `TestService`, and `StatisticsRepo`
- **Input validation** — added `FILTER_VALIDATE_INT` checks on user-supplied parameters (`$_GET['stid']`)
- **REST API auth** — moved authentication from inline checks to proper `permission_callback` on POST endpoints

### Bug Fixes

- **Template test resolution** — null checks in `FrontendBeforeRenderEvent` moved before the hiding loop so template-loaded tests correctly resolve and display the active variant
- **Variation distribution** — replaced `rand(1, 100)` with `mt_rand(1, 10000)` for better precision; added fallback for floating-point edge cases
- **Null reference guards** — protected against null `$targetVariation` in `SendHeadersEvent`
- **JavaScript typo** — fixed `window.window.rocketSplitTest` double-prefix (2 occurrences in `WpHeaderEvent`)
- **Uninitialized global** — protected `$rocketSplitTestRunningTests` with `?? []` to prevent null in `json_encode()`
- **Admin form field naming** — fixed URL input using wrong placeholder token (`VARIATION_ID` instead of `TEST_COUNT`), which caused all dynamically-added URL variations to overwrite each other on save

### Pro-Version Removal

The original plugin gates certain features (cache buster settings, unlimited variations/tests) behind a paid licence. This fork removes those restrictions entirely:

- Removed `LicenceManager` and all licence checks
- Removed upsell banners, "Buy Pro" buttons, and variation count limits
- All features are now available without a licence key

### Cache Buster Replaced with Cache-Control Headers

The original plugin's "cache buster" feature used client-side AJAX to work around CDN caching, causing flash of unstyled content (FOUC) and extra HTTP requests. This fork replaces it with a simpler approach:

- Pages with active split tests automatically send `Cache-Control: no-store, private` headers
- CDN/proxy caches (Cloudflare, etc.) respect these headers and skip caching for tested pages
- No FOUC, no extra requests, no settings required — it just works
- Removed `CacheCheckService`, `ShowCacheWarningMessage`, `ShowWPEngineMessage`, `FrontendAfterRenderSectionEvent`, `SettingsPage`, and all cache buster JS framework code

### Code Cleanup

- Removed dead code, unreachable branches, and commented-out blocks
- Removed unused imports (`FormSubmitEvent`, `ConversionWidget`)
- Removed developer TODO comments
- Cleaned up `editor.min.js` pro-version references

## Installation

1. Download or clone this repository
2. Place the folder in `wp-content/plugins/split-test-for-elementor`
3. Activate the plugin in WordPress admin

If you're replacing the original plugin, deactivate it first. The database schema is identical — no migration needed.

## Versioning

This fork uses the format `{original_version}-fork.{patch}`, e.g. `1.8.4-fork.1`. The WordPress Update URI is set to `false` to prevent automatic updates from overwriting the fork with the original plugin.

## Relationship to the Original

This is an independent community fork. It is **not** affiliated with or endorsed by Rocket Elements. If the original plugin resumes active development, we encourage users to switch back to the official version. The original plugin is available at: [WordPress.org](https://wordpress.org/plugins/split-test-for-elementor/).

## License

GPL-2.0-or-later — see [LICENSE](https://www.gnu.org/licenses/gpl-2.0.html). The original plugin was distributed on WordPress.org, which [requires GPL-compatible licensing](https://developer.wordpress.org/plugins/wordpress-org/detailed-plugin-guidelines/#1-plugins-must-be-compatible-with-the-gnu-general-public-license) for all hosted plugins.
