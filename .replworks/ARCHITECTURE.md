# ARCHITECTURE.md

## Purpose

- This document defines the structure of replworks/laravel-kr-mail-theme (the Package): components, responsibilities, data, and flows.
- Terms are defined in PRODUCT_SPEC.md. Technologies are fixed in TECH_STACK.md.
- Every FR, ERR, SCR, and FLOW in PRODUCT_SPEC.md is assigned to a component in Traceability.
- Items marked [UNVERIFIED] depend on an unchecked external behavior. The task named in TASKS.md checks it.

## Boundaries

- Inside the Package: the Theme, Markdown conversion to HTML part and TEXT part, subject and header rules, Unsubscribe Link generation, Unsubscribe request handling, Consent Records, Unsubscribe Event, Workbench, automated checks.
- Inside the Service: Mail Body wording (Markdown), Brand Information values (environment or config), choice of Mail Kind, sending the mail, listening to the Unsubscribe Event.
- Outside both: collecting prior consent, sending hours, filtering delivery by consent value (PRODUCT_SPEC Non-Goals).
- Dependency direction: `Workbench` and `Checks` depend on `src`. `src` never depends on them. No code in `src` sends mail, which guarantees FR-042.

## Component Overview

```
Service Mailable / Workbench / Checks
              |   Markdown (from a Blade view), Mail Kind, Subject, user id
              v
        MailRenderer -------> BrandInformation (config + env, Placeholders)
         |    |    |
         |    |    +--------> SubjectFormatter
         |    +-------------> MarkdownConverter --> HTML renderer (button rule)
         |                                     \--> TEXT renderer (tree walker)
         v
   Theme layouts (HTML) + TextLayout (TEXT) + CssInliner
         |
         v
   RenderedMail { subject, html, text, headers }

   UnsubscribeUrlGenerator              Recipient mail client
   (used by MailRenderer)                       |  GET / POST
                                                v
                                       UnsubscribeController --> ConsentRepository --> kr_mail_consents
                                                |                       |
                                                v                       v
                                         SCR-002 view          Unsubscribed event --> Service listeners
```

## Components

### KrMailThemeServiceProvider

- Merges default configuration. Registers the views namespace `kr-mail-theme`, migrations, and the Unsubscribe route.
- The route path is read from configuration at boot (Unsubscribe Path, default `unsubscribe`).
- Publishing configuration and migrations is optional. Nothing is required to be copied into the Service (FR-038).

### Configuration (`config/kr-mail-theme.php`)

- Flat keys, one per field, each reading an environment variable (FR-039):

| Key                          | Environment variable                 | When not supplied         |
| ---------------------------- | ------------------------------------ | ------------------------- |
| service_name                 | KR_MAIL_SERVICE_NAME                 | Placeholder `[서비스명]`  |
| logo                         | KR_MAIL_LOGO_URL                     | Placeholder text `[로고]` |
| company_name                 | KR_MAIL_COMPANY_NAME                 | not shown                 |
| customer_center              | KR_MAIL_CUSTOMER_CENTER              | not shown                 |
| business_registration_number | KR_MAIL_BUSINESS_REGISTRATION_NUMBER | not shown                 |
| address                      | KR_MAIL_ADDRESS                      | not shown                 |
| terms_url                    | KR_MAIL_TERMS_URL                    | not shown                 |
| unsubscribe_path             | KR_MAIL_UNSUBSCRIBE_PATH             | default `unsubscribe`     |

- A Service config file named `kr-mail-theme.php` overrides keys one by one (FR-040). Because the keys are flat, overriding one key keeps the others.
- Placeholders live in BrandInformation, not in the config file, so an empty string in the environment still counts as not supplied.

### BrandInformation

- Immutable value object built from configuration.
- A field is not supplied when it is null or an empty string. Service Name and Logo take their Placeholders (FR-003, FR-004). The five footer fields are exposed as null and the layouts and the TEXT layout skip them, including their labels (FR-005, FR-006).
- Never raises an error (FR-008, ERR-002).

### MailKind

- Enum with `Informational` and `Advertising`.

### SubjectFormatter

- Informational: Final Subject equals Mail Subject (FR-026).
- Advertising: `(광고) ` plus Mail Subject. No second prefix when the subject already begins with `(광고)` (FR-025, FR-027).

### MarkdownConverter

- Parses the Mail Body Markdown once into a syntax tree. Raw HTML in the Markdown is escaped and unsafe link schemes are not rendered.
- HTML renderer: standard conversion, plus one rule: a paragraph whose only child is one link becomes a button (table-based, inline-styled) (FR-030 to FR-032).
- TEXT renderer: walks the same tree and writes plain text (FR-035):
  - paragraph: its text
  - link: `label: URL`, or only the URL when label and URL are equal
  - button: `label: URL` on its own line (a link-only paragraph is its own block)
  - table: one line per row, cells joined by ` | `
  - blockquote: each line prefixed with `> `
  - anything else: its text content
  - blocks are separated by a blank line, and the output ends with a single newline
- [Verified in T-006] One parse feeds both renderers. The HTML renderer overrides the paragraph renderer so a paragraph whose only child is one link becomes a table-based button; the TEXT renderer walks the same `Document`. The forms above are what the spike produces, pinned by `tests/MarkdownConversionTest.php`.

### Theme

- Directory `resources/theme/`:
  - `informational.blade.php`, `advertising.blade.php` (HTML layouts)
  - `text.blade.php` (TEXT layout, used by both kinds, shows the Unsubscribe line only when a link is given)
  - `partials/` header and footer pieces
  - `base.css`, `dark.css`, `mobile.css`
- Only one Theme exists (FR-001). Nothing selects it.

### CssInliner

- Inlines `base.css` into the HTML. Appends `dark.css` inside `@media (prefers-color-scheme: dark)` and `mobile.css` inside a narrow-width media query, as a `<style>` element in `<head>`. With a forced scheme (Workbench preview only), the chosen variant is appended without the media condition.

### UnsubscribeUrlGenerator

- Produces an absolute signed URL to the Unsubscribe route with the Recipient User Identifier as query parameter `user` (FR-016 to FR-018).
- Uses a signature without expiration (FR-019, FR-020).
- The URL is built from the Service's application URL. The Workbench sets its own application URL.

### MailRenderer

- Input: Mail Kind, Mail Subject, Mail Body Markdown, Recipient User Identifier, optional forced color scheme (Workbench preview only).
- Output: `RenderedMail` with `subject`, `html`, `text`, `headers`.
- Steps, in order:
  1. Build BrandInformation (Placeholders applied).
  2. For Advertising Mail, require the Recipient User Identifier (ERR-001).
  3. Compute Final Subject.
  4. Convert the Mail Body: HTML fragment and TEXT fragment.
  5. For Advertising Mail, generate the Unsubscribe Link and the two List-Unsubscribe headers. For Informational Mail, generate neither, even when a Recipient User Identifier is supplied (FR-011, FR-023, FR-024, ERR-004).
  6. Render the HTML layout of the Mail Kind with brand, body fragment, and Unsubscribe Link, then run CssInliner.
  7. Render the TEXT layout with Service Name, body text, footer values, and the Unsubscribe Link for Advertising Mail (FR-034, FR-036, FR-037).
- Rendering fails as a whole on error. No partial output is returned.

### RendersKrTheme (trait for Service Mailables)

- One method for a Mailable: Mail Kind, Mail Subject, Mail Body Blade view and data, Recipient User Identifier, optional Recipient Name.
- Uses BodyViewRenderer to render the view to Markdown, calls MailRenderer, then sets subject, HTML part, TEXT part, and extra headers on the Mailable.
- The Service sends the Mailable with its own mailer.
- Setting the parts and headers on a Mailable (verified live in T-005 against Mailpit v1.30.3 under Laravel 12.70.0 and Laravel 13.37.0; Mailpit shows both parts and both headers):
  - HTML part: `$mailable->html($htmlString)`.
  - TEXT part: `$mailable->text(new \Illuminate\Support\HtmlString($textString))`. A raw TEXT string is passed as an `Htmlable`, so the Mailer returns it unchanged instead of resolving it as a view name.
  - Headers: define `public function headers(): \Illuminate\Mail\Mailables\Headers` returning `new Headers(text: ['List-Unsubscribe' => $url, 'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click'])`.

### BodyViewRenderer

- Renders a Service Blade view with the Service's data plus one extra variable, `$recipientDisplayName`, and returns the Markdown string.
- Display name rule: a Recipient Name that is not null and not an empty string gives the name followed by `님`. Otherwise `회원님` (FR-060, FR-061).
- Used by RendersKrTheme and by the Workbench, so both produce the same Markdown.
- The Service's other dynamic values are ordinary view data. Blade `{{ }}` escapes HTML in them.

### Unsubscribe handling

- Route: GET and POST on the Unsubscribe Path. No `web` group, no login, no CSRF (D-007, FR-045).
- UnsubscribeController:
  1. Check the signature of the full URL. Invalid or unsigned: GET renders SCR-002 in Error state, POST returns 403 (ERR-003). Nothing changes and no event is fired.
  2. Valid: call ConsentRepository, then respond. GET renders SCR-002 in Success state. POST returns 200 (FR-043, FR-044, FR-052, FR-053).
  3. No confirmation step (FR-046). A POST is accepted whether or not the body equals `List-Unsubscribe=One-Click`.
- SCR-002 view: the Theme header and footer around one message slot (EL-012), using the Informational layout parts. It never contains an Unsubscribe Link. Brand Information is read as usual, with Placeholders.

### ConsentRepository

- One operation: `revokeMarketing(userIdentifier): bool`, returning whether the value changed.
  - No row: insert row with `marketing_consent = false`, return true (FR-048).
  - Row with true: conditional update to false, return true when one row was affected.
  - Row with false: return false.
- An insert race is handled by a unique index and a retry as an update.
- The controller fires the Unsubscribed event only when the return value is true (FR-050, FR-051).

### Unsubscribed (event)

- Carries `userIdentifier`. Fired after the database change is committed.

### Data model

Table `kr_mail_consents`:

| Column                 | Type                     | Rule                       |
| ---------------------- | ------------------------ | -------------------------- |
| id                     | big integer, primary key |                            |
| user_identifier        | string                   | unique                     |
| marketing_consent      | boolean                  | stored as boolean (FR-049) |
| created_at, updated_at | timestamps               |                            |

- Rows persist until changed (PRODUCT_SPEC Constraints).
- The Package only writes `false`. Writing `true` is a Non-Goal.

## Workbench (repository only)

- Location: `workbench/` of the repository, run through Testbench.
- Brand Information: a fixed sample set with every field supplied (FR-041). Recipient User Identifier and Recipient Name: fixed sample values.
- Sample Bodies: four Blade views that output Markdown (receipt-style table, button, long paragraph, quotation), rendered with BodyViewRenderer. The long paragraph greets the Recipient Display Name. The same four are used by the automated checks.
- Routes:
  - `GET /` serves SCR-001.
  - `GET /preview` returns the HTML part for the query parameters kind, body, scheme. SCR-001 shows it in an iframe. Viewport width is applied to the iframe (375 px or full width).
  - `GET /meta` returns the Final Subject for EL-005 and the TEXT part for EL-007.
  - `POST /send` renders without a forced scheme, sends over SMTP to Mailpit with both parts, and returns delivered or failed with the ERR ID.
- SCR-001 state handling is in one JavaScript file: Initial, Loading, Populated, Error, Sending, Sent, Send failed.
- EL-009 is enabled only when EL-008 is a valid email address and the state is Populated, Sent, or Send failed (ERR-006).
- Mail transport: SMTP to the local Mailpit only. No other transport is configured (FR-042).
- Mailpit unreachable: `POST /send` returns failed with ERR-005. No retry.
- The Dark preview is display-only. The mail sent by EL-009 uses the normal media-query version.

## Automated Checks (repository only)

- A Pest suite with a dataset of every combination of Mail Kind and Sample Body: 2 × 4 = 8 combinations.
- Per combination:
  1. Render with MailRenderer using the Workbench Brand Information.
  2. Send to Mailpit over SMTP (Mailpit is emptied at the start of the run).
  3. Read the message HTML part (`GET /view/{ID}.html`), TEXT part (`GET /view/{ID}.txt`), headers (`GET /api/v1/message/{ID}/headers`; summary fields `HTML` and `Text` also available at `GET /api/v1/message/{ID}`), and HTML check result (`GET /api/v1/message/{ID}/html-check`) through the Mailpit HTTP API. Message list is `GET /api/v1/messages` and delete-all is `DELETE /api/v1/messages` (verified live in T-003; one real response saved at `tests/Fixtures/mailpit-html-check.json`).
  4. Assert FR-054 from the HTML check result: fail when any `Results[]` entry has `Support: "no"` for every client of a feature (i.e. the `Warnings[]` entry lists a feature with no supporting mail client). The exact field path is `Warnings[].Results[].Support`.
  5. Assert FR-005, FR-006, FR-033, FR-034, FR-035.
  6. Advertising only: assert FR-025, FR-013, FR-021, FR-022, FR-036.
  7. Informational only: assert FR-011, FR-023, FR-024, FR-037.
  8. Compare HTML part and TEXT part with approved files in `tests/Snapshots/<kind>-<body>.html` and `.txt` (FR-058).
- Every assertion message starts with its FR ID, so a failed line identifies the FR.
- Approving a snapshot: the Developer runs the suite with an environment switch that rewrites the approved files, then reviews the diff in Git.
- Exit code: the Pest exit code, 0 only when every check passes (FR-059, ERR-007).
- Cases that are not combinations (Placeholders, footer omission, Recipient Display Name, config precedence, subject prefix, Unsubscribe handling) are normal Pest tests and do not need Mailpit.
- Mailpit not running: every combination fails with a message that Mailpit is unreachable. [Not in PRODUCT_SPEC.]

## Repository Layout

```
src/
  KrMailThemeServiceProvider.php
  MailKind.php
  BrandInformation.php
  MailRenderer.php
  RenderedMail.php
  SubjectFormatter.php
  CssInliner.php
  RendersKrTheme.php
  BodyViewRenderer.php
  Markdown/MarkdownConverter.php, HtmlRenderer.php, TextRenderer.php
  Unsubscribe/UnsubscribeUrlGenerator.php, UnsubscribeController.php,
              ConsentRepository.php, Unsubscribed.php
  Exceptions/MissingRecipientIdentifier.php
config/kr-mail-theme.php
database/migrations/
resources/theme/ (layouts, text layout, partials, base.css, dark.css, mobile.css)
resources/views/unsubscribe.blade.php
routes/unsubscribe.php
workbench/ (app, routes, views, one JS file, sample bodies in Markdown, sample brand)
tests/ (Unit, Feature, Checks, Snapshots)
compose.yaml (Mailpit)
```

## Error Mapping

| ERR     | Raised by             | Visible as                                                                            |
| ------- | --------------------- | ------------------------------------------------------------------------------------- |
| ERR-001 | MailRenderer          | exception naming Recipient User Identifier; Workbench shows it in EL-006              |
| ERR-002 | BrandInformation      | no error; Service Name and Logo Placeholders used, unsupplied footer fields not shown |
| ERR-003 | UnsubscribeController | SCR-002 Error state (GET), 403 (POST)                                                 |
| ERR-004 | MailRenderer          | no error; link and headers omitted                                                    |
| ERR-005 | Workbench `/send`     | EL-010 failed with ERR-005                                                            |
| ERR-006 | SCR-001 script        | EL-009 disabled                                                                       |
| ERR-007 | Pest suite            | failed line and non-zero exit code                                                    |

## Traceability

| PRODUCT_SPEC item              | Component                                                    |
| ------------------------------ | ------------------------------------------------------------ |
| FR-001                         | Theme                                                        |
| FR-002                         | Theme layouts                                                |
| FR-003 to FR-008               | BrandInformation, Theme partials, TEXT layout                |
| FR-009 to FR-013               | Theme layouts (two HTML layouts)                             |
| FR-014, FR-015, FR-028, FR-029 | Theme layouts, dark.css, mobile.css                          |
| FR-016 to FR-020               | UnsubscribeUrlGenerator                                      |
| FR-021 to FR-024               | MailRenderer (headers)                                       |
| FR-025 to FR-027               | SubjectFormatter                                             |
| FR-030 to FR-032               | MarkdownConverter (HTML renderer)                            |
| FR-033 to FR-037               | MarkdownConverter (TEXT renderer), TEXT layout, MailRenderer |
| FR-038 to FR-040               | ServiceProvider, Configuration                               |
| FR-041, FR-042                 | Workbench                                                    |
| FR-043 to FR-053               | UnsubscribeController, ConsentRepository, Unsubscribed       |
| FR-054 to FR-059               | Automated Checks                                             |
| FR-060, FR-061                 | BodyViewRenderer                                             |
| SCR-001, FLOW-002              | Workbench                                                    |
| SCR-002, FLOW-004, FLOW-005    | UnsubscribeController, unsubscribe view                      |
| FLOW-001                       | RendersKrTheme, Configuration                                |
| FLOW-003                       | Automated Checks                                             |

## Open Decisions

- `List-Unsubscribe` value: RFC 2369 wraps the URL in angle brackets. This document assumes `<URL>`, which still contains the Unsubscribe Link (FR-021).
- GET unsubscribe is processed immediately with no confirmation (FR-043, FR-046). A mail scanner that opens links can unsubscribe a Recipient. This follows from the spec and is accepted.
- Blade and Markdown: a Mail Body Blade view that indents Markdown by four spaces turns into a code block. The README documents this (T-029).
- Behavior of the automated checks when Mailpit is not running (see Automated Checks).
- Markdown special characters in dynamic values (for example `*` in a name) are not escaped for Markdown. Blade escapes HTML only.
