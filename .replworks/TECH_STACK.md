# TECH_STACK.md

## Purpose

- This document fixes the technologies of replworks/laravel-kr-mail-theme (the Package).
- Terms are defined in PRODUCT_SPEC.md.
- A technology that is not listed in Runtime, Production Dependencies, Development Dependencies, or Infrastructure MUST NOT be used.
- Adding a technology requires one line in the matching section and one row in Decision Log. The Developer decides.
- Items marked [UNVERIFIED] have not been checked against the real tool. The task named next to each is in TASKS.md.

## Runtime

- Language: PHP. Supported versions: PHP 8.2 and later for Laravel 12, PHP 8.3 and later for Laravel 13 (verified from Packagist require strings `^8.2` and `^8.3`; package minimum is `^8.3`).
- Host framework: Laravel 12 and Laravel 13.
- Package form: Composer library with an auto-discovered service provider.
- Distribution: public GitHub repository. Packagist registration is undecided (see Open Decisions).
- Mail transport used by the Package itself: none. The Package renders mail. The Service sends it with its own mailer.
- Mail transport used by the Workbench: SMTP to Mailpit only (FR-042).

## Production Dependencies

These are installed in every Service.

- laravel/framework. Version constraint: `^12.0|^13.0`. It requires league/commonmark `^2.8.1` and tijsverkoyen/css-to-inline-styles `^2.2.5` in both Laravel 12 (verified v12.70.0) and Laravel 13 (verified v13.37.0).
- league/commonmark: parses the Mail Body Markdown into a syntax tree. Used for both the HTML part and the TEXT part. The tables extension (`TableExtension`) ships inside the package.
- tijsverkoyen/css-to-inline-styles: inlines CSS into the table layout.

No other production dependency is allowed.

## Development Dependencies

- orchestra/testbench: runs the Package inside a Laravel application and provides the Workbench skeleton. Testbench 10 follows Laravel 12 and Testbench 11 follows Laravel 13 (constraint `^10.0|^11.0`).
- pestphp/pest `^4.0` and pestphp/pest-plugin-laravel `^4.0`: test runner for the automated checks (FR-054 to FR-059). Verified working with both Laravel 12 and Laravel 13.
- laravel/pint: code style.

## Infrastructure

- Mailpit (container image `axllent/mailpit`), started by a Compose file in the Package repository.
  - SMTP port for Test Delivery and automated checks.
  - HTTP port for the web interface (EL-011) and the HTTP API used by the automated checks.
  - [UNVERIFIED: HTTP API endpoints for message list, message HTML, message TEXT, message headers, HTML check, and delete-all, and the shape of the HTML check JSON. T-003.]
- Docker (or a compatible container runtime) on the Developer machine.

## Mail Body Conversion

- Input: Markdown produced by a Service Blade view.
- One parse, two renderers: the HTML renderer (with a rule that turns a link-only paragraph into a button) and the TEXT renderer (a walker over the same syntax tree). [UNVERIFIED: league/commonmark extension points are enough for both. T-006.]
- Raw HTML inside the Markdown is escaped, and unsafe link schemes are not rendered as links.
- Markdown elements supported at minimum: paragraph, link, table, blockquote (FR-032). Others (headings, lists, emphasis) are converted if the library does so by default, and are not covered by the automated checks.

## Mail Theme Authoring

- One Theme in the repository. No Theme selection mechanism.
- Layout: HTML tables with inline styles. No flexbox, no grid.
- CSS source: `base.css` (inlined), `dark.css` and `mobile.css` (kept in a `<style>` element, not inlined).
- Dark color scheme: CSS `prefers-color-scheme` media query plus `color-scheme` meta. [UNVERIFIED: whether the Mailpit HTML check reports `prefers-color-scheme` as unsupported in some client. T-004. FR-054 depends on the result.]
- Font stack: `Apple SD Gothic Neo`, `Malgun Gothic`, `Noto Sans KR`, then generic sans-serif. No web fonts.
- Images: Logo is referenced by absolute URL supplied through Brand Information. The Package embeds no image. The Logo Placeholder is text.

## Configuration

- One config file in the Package (`kr-mail-theme`) with flat top-level keys, one per Brand Information field, each reading an environment variable. A Service config file with the same file name overrides keys one by one.
- Flat keys are required because Laravel's config merge for packages is shallow. A nested `brand` array in the Service file would replace all brand defaults at once.
- Environment variable names are listed in ARCHITECTURE.md.

## Not Used

- Laravel Markdown mail components (`mail::message` and the like), `mail.markdown.*` configuration, and Laravel Markdown mail themes. See D-001.
- Livewire, Vue, React, Alpine, Tailwind, Vite, npm.
- MJML and any other mail templating compiler.
- Spatie snapshot packages. See D-005.
- Real mail services (Resend, SES, and so on) in the Package or the Workbench.
- Static analysis tools (PHPStan, Larastan).
- A database model class for Consent Records. See D-004.

## Decision Log

| ID    | Decision                                                                                                                                              | Reason                                                                                                                                                                                                                               | Rejected                                                                                            |
| ----- | ----------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ | --------------------------------------------------------------------------------------------------- |
| D-001 | Layouts are plain Blade views rendered by the Package's own renderer. Markdown is used only to convert the Mail Body.                                 | Two layouts (Informational, Advertising), dark and mobile CSS kept out of the inliner, and a TEXT part with footer and unsubscribe content all need control that the Markdown mail components and global Markdown theme do not give. | Laravel Markdown mail theme (`mail.markdown.paths`, `mail.markdown.theme`) and `mail::` components. |
| D-002 | Own CSS inlining step using tijsverkoyen/css-to-inline-styles.                                                                                        | Plain Blade views are not inlined by Laravel. Media-query CSS must stay in the document.                                                                                                                                             | Writing every style inline by hand.                                                                 |
| D-003 | Workbench is a Testbench application inside the Package repository and is not installed in Services.                                                  | The Developer previews and tests without a host Service.                                                                                                                                                                             | A separate demo Laravel application.                                                                |
| D-004 | Consent Records use one table accessed through the query builder.                                                                                     | One boolean per user identifier. An atomic conditional update is needed for FR-050 and FR-051.                                                                                                                                       | Eloquent model.                                                                                     |
| D-005 | Snapshot comparison is a small helper in the test suite. Approved HTML and TEXT are stored as files in the repository.                                | One comparison rule, no extra dependency.                                                                                                                                                                                            | spatie/phpunit-snapshot-assertions.                                                                 |
| D-006 | Automated checks are a Pest suite with one dataset entry per combination.                                                                             | Pest prints one line per dataset entry and returns a non-zero exit code on failure.                                                                                                                                                  | A custom Artisan command.                                                                           |
| D-007 | The Unsubscribe route is registered by the Package and does not use the `web` middleware group.                                                       | A POST from a mail client has no CSRF token and no session.                                                                                                                                                                          | Asking the Service to exclude the route from CSRF verification.                                     |
| D-008 | One Markdown parse feeds both the HTML part and the TEXT part through two renderers.                                                                  | The TEXT part never contains Markdown syntax or HTML, and the two parts cannot drift apart.                                                                                                                                          | Stripping tags from the HTML part. Showing Markdown source as the TEXT part.                        |
| D-009 | One built-in Theme, no Theme Name setting.                                                                                                            | The Package is used by its author first. A single Theme removes a registry, a selector, and an error path. A second Theme can be added later as a new decision.                                                                      | Theme registry with named Themes.                                                                   |
| D-010 | Service Name and Logo have Placeholders. The five footer fields have none and are not shown when not supplied. A missing field never fails rendering. | Mail can be sent and previewed before any configuration. A placeholder in a footer would look like real sender information.                                                                                                          | Failing on a missing field. Placeholders for footer fields.                                         |
| D-011 | The Package computes the Recipient Display Name (`{name}님` or `회원님`) and passes it to the Mail Body view.                                         | One Korean-specific rule in one place. The Service can still pass any other dynamic data to its view.                                                                                                                                | Each Service writing the fallback itself.                                                           |

## Open Decisions

- Packagist registration: needed for plain `composer require`. Without it the Service adds a VCS repository entry.
- Advertising Mail with no Company Name or Customer Center Contact: the footer shows neither and the mail is sent (FR-005, FR-006). The Package does not warn or fail. A log warning could be added later if needed.
