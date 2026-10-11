## Change Log

## [Unreleased]

## [1.2.8] — 2026-09-27

### Fixed
- Repackaged release: the 1.2.7 package on WordPress.org mistakenly contained a nested `trunk` folder. No functional changes.

## [1.2.7] — 2026-09-27

### Changed
- The StoreEngine licence SDK is now loaded by requiring its `init.php` directly instead of relying on Composer's `files` autoload. Composer de-duplicates autoload-files by a package-stable hash, so when several active plugins each ship the SDK only the first plugin's copy ran — every later copy was silently skipped and never registered its version, meaning a newer bundled SDK could lose the "newest version wins" election it never entered. Matches how StoreEngine core and Academy already load it.
- Declared `critical_paths` for the licence SDK's pre-swap update validation, so an incomplete or truncated update package is rejected before WordPress swaps the plugin folder rather than leaving the plugin half-installed.
- Updated the bundled StoreEngine licence SDK to 1.5.9: no more licence-server calls on every admin page load, background update checks are now batched, and a site-wide circuit breaker stops hammering the licence server once it's down.
- Refactored the ECM React Editor's styles: theming now runs on CSS variables in `ecm-react-editor` and its RTL stylesheet, and WordPress admin-chrome overrides were split out of the global SCSS into their own `_page-chrome.scss`, imported consistently across the backend, block editor, and classic editor bundles.
- Pinned the dev Composer platform to PHP 8.0 so `composer install` no longer aborts on PHP below 8.4 when the lockfile resolves a PHP-8.4-only transitive dev dependency; production installs (`--no-dev`) were unaffected.

### Fixed
- Fixed taxonomies attached to another plugin's post type (e.g. an LMS's course post type) not getting an admin submenu under that plugin's explicit menu parent, since only post types — not taxonomies — get their `show_in_menu` string auto-resolved by WordPress core.
- Fixed the post list losing its current page, search term, status filter, and sort order when its data refreshed (e.g. after a create, duplicate, or delete), and enabled translations for all JavaScript bundles.

## [1.2.6] — 2026-08-25

### Security
- Settings update no longer accepts an arbitrary post ID; the post-type guard that `create` and `delete` already had is enforced on `update` too, so a mistyped or crafted ID can no longer overwrite an unrelated post's title, slug and content.
- Reordering post priorities now requires permission to edit other people's posts of that type, and skips any individual post the current user cannot edit, instead of renumbering every published post after checking only the one being moved.
- The front-end script payload no longer publishes every custom post type's configuration and every field group's full definition (drafts included) to anonymous visitors. It now ships only the post types the front-end submission form is configured to accept, and only published field groups bound to them.
- `profile.php` — reachable by every logged-in role — no longer receives the admin app's structural payload. Users without Content Manager access get only the user-scoped field groups the profile form renders.
- The `manage_options` fallback in ECM's capability checks is now bounded to the pre-migration window and to administrators/super admins, so revoking a capability in Settings → Permissions is actually enforced for any other role that happens to hold `manage_options`.
- Import no longer accepts a caller-supplied file path over AJAX; the archive always comes from the request's own upload.
- Front-end submission now writes only meta keys registered for the target post type, requires the submitter to own (or be able to edit) an attachment before it can be set as the featured image, and rate-limits submissions per IP.
- Reactions are validated against the configured reaction set and require read access to the target post, instead of storing any arbitrary key in post and user meta.
- Imported attachment captions and descriptions go through the same kses pass as imported post content.
- `ecm_get_user_fields()` returns only ECM user-field meta rather than the user's entire meta row (`wp_capabilities`, `session_tokens`, and other plugins' data).
- SVG icon output in front-end templates is escaped through `Helper::kses_svg()`.

### Fixed
- Fixed `[ecm_field key="…" post_id="…"]` rendering nothing for logged-out visitors: the read check resolved to a capability anonymous users never hold. Public posts are now readable by everyone, and non-public statuses still require permission.
- Fixed front-end submission stripping all formatting and line breaks from post content before saving.
- Fixed export downloads requiring `manage_options` while generating an export only required Manage Tools, so a Manage Tools user got a 403 on their own archive.
- Fixed imported post types going up to 24 hours without `ecm_priority` registered, silently disabling priority ordering after an import.
- Fixed `SeLicense::is_pro()` throwing — and taking the site down — when the licence SDK is missing or not yet initialised; it now degrades to "not pro".
- Fixed a settings screen with no slug producing a malformed admin route and PHP warnings.
- Fixed a fatal when a field preset contained a Pro-only field type on a free site.
- Fixed PHP warnings from single-image user fields and from front-end submission settings when the option is unset.
- Added `function_exists()` guards to the `ecm_*` template API functions.
- Remaining untranslated strings in the bookmark, claim, reaction, upvote and review addons.
- Fixed non-admin users with the `manage_categories` capability (e.g. category managers) being unable to create categories.
- Fixed React admin routes not enforcing capability checks; unauthorized requests are now redirected and only routes/menu items the current user has permission to access are exposed.
- Fixed manually typed or drag-reordered post priorities still colliding with existing values when a search filter or pagination hid the rest of the list; priority moves are now resolved against the complete list on the server so priorities always stay unique.
- Fixed notification message text, icon, and close button being hardcoded to white, making them unreadable against light backgrounds (most notably the warning notification's light orange background).
- Fixed some static text was not translated.
- Fixed chakra theme variable conflict with css root variables.

### Added
- Added What's new section into Topbar

## [1.2.5] — 2026-07-31

### Added
- Custom post type entries (posts, custom field values, featured image, and taxonomy terms) are now included when exporting or importing "All" or "Post Types".

### Changed
- Front-end scripts and styles are now only enqueued on pages that actually use an ECM shortcode, instead of on every page.
- Export archives are written to a protected directory with an unguessable filename instead of a predictable path in the uploads root.
- Imported content is restricted to ECM-owned post types and to registered custom field meta keys.

### Fixed
## [1.2.5] — 2026-08-11

### Security
- Hardened AJAX and REST endpoints across the plugin and its addons (bookmark, claim, upvote, reactions, view counter, reviews) with nonce verification and capability/permission checks.
- Added input sanitization (text, email, URL) and output escaping across request handlers and frontend templates.
- Sanitized custom CSS color settings to prevent style/markup injection.
- Applied field-type-aware sanitization when saving custom field and post meta values.

### Fixed
- Fixed script and style asset loading issues.
- Fixed custom post type entries (posts, custom field values, featured image, and taxonomy terms) not being included when exporting or importing "All" or "Post Types".
- Fixed user profile custom fields save process being able to overwrite unrelated WordPress user data, including user roles.
- Fixed Gallery and Image custom field values not saving correctly when used on the user profile screen.
- Fixed broken image URL for Gallery custom fields holding more than one image on the user profile screen.
- Fixed the True/False field default message returning translation file metadata instead of an empty string.
- Fixed anonymous front-end submission being rejected even when the setting was enabled.

## [1.2.4] — 2026-06-14

### Added
- Added clear, plain-language disclosure of the data shared when submitting deactivation feedback.
- Added "External Services" documentation for the store.kodezen.com connection (updates, license validation, optional feedback).

### Changed
- Plugin updates now only install packages served from the official update server (supply-chain hardening).

### Fixed
- Fixed deactivation feedback popup not appearing on the Plugins screen.

## [1.2.3] — 2026-06-4

### Fixed
- Fixed image upload field page blank issue in Single Image Custom Field.
- Fixed image delete issue from Single Image Custom Field.

## [1.2.2] — 2026-04-22

### Added
- Added Storeengine SDK License feature.

## [1.2.0] — 2026-03-02

### Added
- Added User review list feature.
- Added User review list shortcode docs.
- Added ECM data export feature.
- Added ECM data import feature.
- Added tools page.
- Added create/update wp user with custom meta.
- Added Frontend submission feature.
- Added Role control in frontend submission.
- Added submission status control in frontend submission.
- Added Anonymous Frontend Submission control in frontend submission settings
- Added claim count system with claim addon.
- Added claim count shortcode docs.
- Added bookmark count system with bookmark addon.
- Added bookmark count shortcode  docs.
- Added shortcode docs into tools page.
- Added copy-to-clipboard functionality for shortcodes on the Shortcode page.

### Fixed
- Fixed Settings Builder routing issue in dynamic settings.
- Fixed Settings Builder data save issue from dynamic settings.
- Fixed post menu sort order ajax request payload.
- Fixed Category page blank issue.
- Fixed Review form UI break issue.
- Fixed Bookmark status mismatch issue.
- Fixed ECM bookmark addon shortcode not working in frontend.
- Fixed custom taxonomy menu order sort issue.

### Changed
- Changed Claim shortcode docs.
- Changed Separated the addons menu from the main settings.
- Changed ECM settings data save structure.

### Removed
- Removed enable specific post type option for addons from addons settings.